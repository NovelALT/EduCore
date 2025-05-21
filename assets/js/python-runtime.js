class PythonRuntime {
    constructor() {
        this.isReady = false;
        this.initRuntime();
    }

    initRuntime() {
        document.addEventListener('py-ready', () => {
            console.log('Python runtime ready');
            this.isReady = true;
        });
    }

    async execute(code, outputId) {
        const outputElement = document.getElementById(outputId);
        outputElement.innerHTML = 'Running...';

        if (!this.isReady) {
            outputElement.innerHTML = 'Waiting for Python runtime...';
            await new Promise(resolve => {
                const checkReady = setInterval(() => {
                    if (this.isReady) {
                        clearInterval(checkReady);
                        resolve();
                    }
                }, 100);
            });
        }

        window.pyExecute(code, outputId);
    }
}

window.pythonRuntime = new PythonRuntime();
