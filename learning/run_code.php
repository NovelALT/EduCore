<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

$input = json_decode(file_get_contents('php://input'), true);
$code = $input['code'] ?? '';
$userInput = $input['input'] ?? '';

if (empty($code)) {
    exit(json_encode(['success' => false, 'message' => 'No code provided']));
}

try {
    $tempFile = tempnam(sys_get_temp_dir(), 'python_');
    
    $wrappedCode = <<<PYTHON
import sys
import traceback
import math
import operator

class PythonInterpreter:
    def __init__(self):
        # เพิ่ม __builtins__ เพื่อให้ใช้งาน operators ได้
        self.globals = {
            '__builtins__': __builtins__,
            # Built-in functions
            'print': print,
            'input': input,
            'str': str,
            'int': int,
            'float': float,
            'bool': bool,
            'list': list,
            'dict': dict,
            'len': len,
            # Math functions
            'abs': abs,
            'pow': pow,
            'round': round,
            'max': max,
            'min': min,
            'sum': sum,
            # Math constants
            'pi': math.pi,
            'e': math.e,
            # Math module functions
            'sqrt': math.sqrt,
            'sin': math.sin,
            'cos': math.cos,
            'tan': math.tan,
        }
        
    def get_type(self, obj):
        type_name = type(obj).__name__
        return f"<class '{type_name}'>"
        
    def run_code(self, code):
        self.globals['type'] = self.get_type
        try:
            # ถ้าเป็นการคำนวณ ให้แสดงผลลัพธ์ด้วย
            result = eval(code, self.globals)
            if result is not None:
                print(result)
        except:
            # ถ้าไม่ใช่การคำนวณ ให้รันโค้ดปกติ
            try:
                exec(code, self.globals)
            except Exception as e:
                print(f"Error: {str(e)}")
                traceback.print_exc()

# Setup UTF-8
sys.stdout.reconfigure(encoding='utf-8')
sys.stderr.reconfigure(encoding='utf-8')

# Create interpreter and run code
interpreter = PythonInterpreter()
interpreter.run_code('''{$code}''')
PYTHON;

    file_put_contents($tempFile, $wrappedCode);
    
    // Set up process
    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );

    $cmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' 
        ? sprintf('python -X utf8 -u "%s"', $tempFile)
        : sprintf('python3 -u "%s"', $tempFile);

    $process = proc_open($cmd, $descriptorspec, $pipes);

    if (is_resource($process)) {
        stream_set_blocking($pipes[1], true);
        stream_set_blocking($pipes[2], true);

        if (!empty($userInput)) {
            fwrite($pipes[0], $userInput . "\n");
            fflush($pipes[0]);
        }
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);
        
        $returnValue = proc_close($process);
        unlink($tempFile);

        $outputText = mb_convert_encoding($output, 'UTF-8', 'UTF-8');
        $errorText = mb_convert_encoding($error, 'UTF-8', 'UTF-8');

        echo json_encode([
            'success' => true,
            'output' => $outputText,
            'error' => !empty($errorText) ? $errorText : null,
            'returnCode' => $returnValue
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
    } else {
        throw new Exception('Failed to start process');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
