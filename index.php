<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codly</title>
    <link rel="stylesheet" href="assets/css/preload.css">
    <link rel="stylesheet" href="assets/main.css">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="preloader">
        <div class="loader"></div>
        <div class="preloader-text">Codly</div>
    </div>
    <nav class="navbar">
        <a href="/" class="logo">Codly</a>
        <div class="mobile-menu-btn">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <ul class="nav-links">
            <li><a href="#home">หน้าแรก</a></li>
            <li><a href="#details">รายละเอียด</a></li>
            <li><a href="#manual">คู่มือ</a></li>
            <li><a href="#faq">FAQ</a></li>
            <li class="auth-wrapper">
                <button class="auth-btn">
                    <i class="fas fa-user-circle"></i>
                    <span>บัญชีผู้ใช้</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="auth-dropdown">
                    <a href="auth/login" class="auth-item">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>เข้าสู่ระบบ</span>
                    </a>
                    <div class="auth-divider"></div>
                    <a href="auth/register" class="auth-item">
                        <i class="fas fa-user-plus"></i>
                        <span>ลงทะเบียน</span>
                    </a>
                </div>
            </li>
        </ul>
    </nav>

    <main>
        <section id="home" class="section hero-section">
            <div class="background-container">

                <div class="mobile-fallback"></div>
                <video autoplay muted loop id="myVideo" playsinline preload="auto">
                    <source src="assets/video/bg-hero.mp4" type="video/mp4">
                </video>
                <div class="overlay gradient-overlay"></div>
                <div id="particles-js"></div>
            </div>
            
            <div class="hero-content">
                <div class="hero-container">
                    <div class="hero-left">
                        <div class="hero-header reveal-up">

                            <h1 class="hero-title">
                                <div class="title-wrapper">
                                    <span class="title-top reveal-right">ก้าวสู่การเป็น</span>
                                    <span class="title-main gradient-animated">Developer</span>
                                    <span class="title-caption reveal-left">ที่เก่งและมั่นใจ</span>
                                </div>
                            </h1>

                            <p class="hero-subtitle reveal-up" data-delay="0.4">
                                เรียนรู้การเขียนโค้ดผ่านระบบที่ออกแบบมาเพื่อผู้เริ่มต้น 
                                <span class="highlight">พร้อมโปรเจคจริง</span>
                            </p>

                            <div class="hero-features reveal-right" data-delay="0.6">
                                <div class="feature-grid">
                                    <div class="feature-item">
                                        <div class="feature-icon-wrapper">
                                            <i class="fas fa-code"></i>
                                            <div class="feature-glow"></div>
                                        </div>
                                        <div class="feature-content">
                                            <h4>เรียนรู้แบบ Interactive</h4>
                                            <p>เรียนรู้ผ่านระบบที่ออกแบบมาเพื่อผู้เริ่มต้น</p>
                                            <div class="feature-stats">
                                                <span class="stat"><i class="fas fa-users"></i> 1+ Students</span>
                                                <span class="stat"><i class="fas fa-star"></i> 5</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="feature-item">
                                        <div class="feature-icon-wrapper">
                                            <i class="fas fa-project-diagram"></i>
                                            <div class="feature-glow"></div>
                                        </div>
                                        <div class="feature-content">
                                            <h4>สร้างโปรเจคจริง</h4>
                                            <p>ฝึกทำโปรเจคจริงพร้อมคำแนะนำ</p>
                                            <div class="feature-stats">
                                                <span class="stat"><i class="fas fa-code-branch"></i> 5+ Projects</span>
                                                <span class="stat"><i class="fas fa-clock"></i> 10+ hrs</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="hero-cta reveal-up" data-delay="0.8">
                                <a href="#courses" class="cta-primary pulse">
                                    <span class="cta-icon">
                                        <i class="fas fa-play"></i>
                                    </span>
                                    <span class="cta-text">
                                        <span>เริ่มเรียนฟรี</span>
                                        <span class="cta-sub">ไม่มีค่าใช้จ่าย</span>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="details" class="section details-section">
            <div class="moving-bg"></div>
            <div class="details-container">
                <div class="section-header reveal-up">
                    <div class="header-decoration">
                        <span class="line"></span>
                        <i class="fas fa-code"></i>
                        <span class="line"></span>
                    </div>
                    <h2>รายละเอียดบริการของเรา</h2>
                    <p class="section-subtitle">เรียนรู้การเขียนโค้ดกับ Codly ได้มากกว่าที่คิด</p>
                </div>
                
                <div class="code-learning-section reveal-up">
                    <div class="code-editors">
                        <div class="editor html-editor">
                            <div class="editor-header">
                                <div class="dots">
                                    <span class="dot red"></span>
                                    <span class="dot yellow"></span>
                                    <span class="dot green"></span>
                                </div>
                                <span class="file-name">index.html</span>
                                <div class="editor-actions">
                                    <button class="copy-btn" data-editor="html">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="editor-content">
                                <textarea class="code-input html" spellcheck="false">&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;My First Page&lt;/title&gt;
    &lt;link rel="stylesheet" href="style.css"&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;div class="container"&gt;
        &lt;h1&gt;Hello, World!&lt;/h1&gt;
        &lt;p&gt;Welcome to Codly&lt;/p&gt;
    &lt;/div&gt;
&lt;/body&gt;
&lt;/html&gt;</textarea>
                            </div>
                        </div>
                        <div class="editor css-editor">
                            <div class="editor-header">
                                <div class="dots">
                                    <span class="dot red"></span>
                                    <span class="dot yellow"></span>
                                    <span class="dot green"></span>
                                </div>
                                <span class="file-name">style.css</span>
                                <div class="editor-actions">
                                    <button class="copy-btn" data-editor="css">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="editor-content">
                                <textarea class="code-input css" spellcheck="false">.container {
    background: linear-gradient(45deg, #FF6B6B, #4ECDC4);
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

h1 {
    color: white;
    font-size: 2em;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

p {
    color: #f8f9fa;
    font-size: 1.2em;
}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="preview-section">
                        <div class="preview-header">
                            <span>Live Preview</span>
                            <div class="preview-controls">
                                <button class="reset-btn">Reset <i class="fas fa-undo"></i></button>
                                <button class="run-btn">Run Code <i class="fas fa-play"></i></button>
                            </div>
                        </div>
                        <div class="preview-content">
                            <iframe id="preview-frame" sandbox="allow-same-origin"></iframe>
                        </div>
                    </div>
                </div>

                <div class="details-grid">
                    <div class="detail-card reveal-left" data-aos="fade-up">
                        <div class="detail-icon-wrapper">
                            <div class="detail-icon">
                                <i class="fas fa-laptop-code"></i>
                            </div>
                            <div class="icon-ring"></div>
                            <div class="icon-particles"></div>
                        </div>
                        <h3>เรียนได้ทุกที่ทุกเวลา</h3>
                        <p>เรียนรู้ผ่านระบบที่ออกแบบมาให้เข้าใจง่าย พร้อมตัวอย่างโค้ดและคำอธิบายละเอียด</p>
                        <div class="feature-list">
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>เรียนออนไลน์ 24/7</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>ตัวอย่างโค้ดครบครัน</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>เรียนซ้ำได้ไม่จำกัด</span>
                            </div>
                        </div>
                        <div class="card-hover-effect"></div>
                    </div>

                    <div class="detail-card reveal-up" data-aos="fade-up" data-aos-delay="200">
                        <div class="detail-icon-wrapper">
                            <div class="detail-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="icon-ring"></div>
                            <div class="icon-particles"></div>
                        </div>
                        <h3>หลักสูตรที่ทันสมัย</h3>
                        <p>เนื้อหาอัพเดตใหม่ตลอด ครอบคลุมทั้ง Front-end และ Back-end Development</p>
                        <div class="tech-stack-grid">
                            <div class="tech-item" data-tooltip="HTML5">
                                <i class="fab fa-html5"></i>
                                <span class="tech-name">HTML5</span>
                            </div>
                            <div class="tech-item" data-tooltip="CSS3">
                                <i class="fab fa-css3-alt"></i>
                                <span class="tech-name">CSS3</span>
                            </div>
                            <div class="tech-item" data-tooltip="JavaScript">
                                <i class="fab fa-js"></i>
                                <span class="tech-name">JavaScript</span>
                            </div><br>
                            <div class="tech-item" data-tooltip="Python">
                                <i class="fab fa-python"></i>
                                <span class="tech-name">Python</span>
                            </div>
                        </div>
                        <div class="card-hover-effect"></div>
                    </div>

                    <div class="detail-card reveal-right" data-aos="fade-up" data-aos-delay="400">
                        <div class="detail-icon-wrapper">
                            <div class="detail-icon">
                                <i class="fas fa-gamepad"></i>
                            </div>
                            <div class="icon-ring"></div>
                            <div class="icon-particles"></div>
                        </div>
                        <h3>เรียนรู้แบบสนุก</h3>
                        <p>เรียนรู้ผ่านการเล่นเกม แก้โจทย์ปัญหาในรูปแบบที่สนุก พร้อมระบบแข่งขัน</p>
                        <div class="game-features">
                            <div class="game-badge">
                                <i class="fas fa-trophy"></i>
                                <span>รางวัล</span>
                                <div class="badge-glow"></div>
                            </div>
                            <div class="game-badge">
                                <i class="fas fa-chart-line"></i>
                                <span>อันดับ</span>
                                <div class="badge-glow"></div>
                            </div>
                            <div class="game-badge">
                                <i class="fas fa-puzzle-piece"></i>
                                <span>โจทย์</span>
                                <div class="badge-glow"></div>
                            </div>
                        </div>
                        <div class="card-hover-effect"></div>
                    </div>
                </div>
            </div>
        </section>
        <section id="manual" class="section manual-section">
            <div class="background-container">
                <div class="mobile-fallback"></div>
                <video autoplay muted loop id="manualVideo" playsinline preload="auto">
                    <source src="assets/video/bg-hero.mp4" type="video/mp4">
                </video>
                <div class="manual-overlay"></div>
            </div>
            <div class="manual-container">
                <div class="manual-header">
                    <h2 class="manual-title">คู่มือการใช้งาน</h2>
                    <p class="manual-subtitle">เรียนรู้วิธีการใช้งานแพลตฟอร์มของเราผ่านคู่มือที่ละเอียดและเข้าใจง่าย</p>
                </div>
                <div class="manual-grid">
                    <div class="manual-card">
                        <div class="card-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3 class="card-title">เริ่มต้นใช้งาน</h3>
                        <p class="card-description">ขั้นตอนการสมัครและเริ่มต้นใช้งานระบบ</p>
                        <ul class="steps-list">
                            <li class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <div class="step-title">สมัครสมาชิก</div>
                                    <p class="step-description">ลงทะเบียนด้วยบัญชี Google ของคุณ</p>
                                </div>
                            </li>
                            <li class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <div class="step-title">เลือกคอร์ส</div>
                                    <p class="step-description">เลือกคอร์สที่คุณสนใจเรียน</p>
                                </div>
                            </li>
                            <li class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <div class="step-title">เริ่มเรียน</div>
                                    <p class="step-description">เริ่มต้นการเรียนรู้ได้ทันที</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="manual-card">
                        <div class="card-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <h3 class="card-title">การใช้งานโค้ดเอดิเตอร์</h3>
                        <p class="card-description">วิธีการใช้งานโค้ดเอดิเตอร์ในระบบ</p>
                        <ul class="steps-list">
                            <li class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <div class="step-title">เขียนโค้ด</div>
                                    <p class="step-description">เขียนโค้ดในช่องเอดิเตอร์</p>
                                </div>
                            </li>
                            <li class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <div class="step-title">รันโค้ด</div>
                                    <p class="step-description">กดปุ่ม Run เพื่อรันโค้ดของคุณ</p>
                                </div>
                            </li>
                            <li class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <div class="step-title">ดูผลลัพธ์</div>
                                    <p class="step-description">ตรวจสอบผลลัพธ์ในหน้าพรีวิว</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="manual-card">
                        <div class="card-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3 class="card-title">ระบบแข่งขัน</h3>
                        <p class="card-description">วิธีการเข้าร่วมการแข่งขันในระบบ</p>
                        <ul class="steps-list">
                            <li class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <div class="step-title">เลือกโจทย์</div>
                                    <p class="step-description">เลือกโจทย์ที่ต้องการทำ</p>
                                </div>
                            </li>
                            <li class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <div class="step-title">ส่งคำตอบ</div>
                                    <p class="step-description">ส่งคำตอบเพื่อตรวจสอบ</p>
                                </div>
                            </li>
                            <li class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <div class="step-title">รับคะแนน</div>
                                    <p class="step-description">รับคะแนนและเหรียญรางวัล</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <section id="faq" class="section faq-section">
            <div class="faq-container">
                <div class="faq-header">
                    <div class="faq-header-content">
                        <div class="header-separator">
                            <span class="line"></span>
                            <i class="fas fa-question-circle"></i>
                            <span class="line"></span>
                        </div>
                        <h2 class="faq-header-title">คำถามที่พบบ่อย</h2>
                        <p class="faq-header-subtitle">มีข้อสงสัย? เรายินดีตอบทุกคำถาม</p>
                        <p class="faq-header-description">ค้นหาคำตอบสำหรับคำถามที่พบบ่อยเกี่ยวกับการเรียนกับ Codly</p>
                    </div>
                    <div class="faq-search">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="ค้นหาคำถามที่คุณสงสัย...">
                        </div>
                    </div>
                </div>
                <div class="faq-list">
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>🎯 Codly เหมาะกับใครบ้าง?</span>
                            <div class="faq-toggle"></div>
                        </div>
                        <div class="faq-answer">
                            Codly เหมาะสำหรับทุกคนที่สนใจการเขียนโค้ด ไม่ว่าจะเป็น:
                            <ul>
                                <li>ผู้เริ่มต้นที่ไม่มีพื้นฐานการเขียนโค้ดมาก่อน</li>
                                <li>นักเรียน ที่ต้องการหาภาษาใหม่ในการเรียน</li>
                                <li>ผู้ที่ต้องการพัฒนาทักษะการเขียนโค้ดให้ดียิ่งขึ้น</li>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>💰 มีค่าใช้จ่ายในการใช้งานหรือไม่?</span>
                            <div class="faq-toggle"></div>
                        </div>
                        <div class="faq-answer">
                            เราให้บริการฟรีสำหรับ:
                            <ul>
                                <li>การเรียนรู้พื้นฐานทั้งหมด</li>
                                <li>แบบฝึกหัดและโจทย์ปัญหา</li>
                            </ul>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>🚀 ใช้เวลานานแค่ไหนในการเรียนรู้?</span>
                            <div class="faq-toggle"></div>
                        </div>
                        <div class="faq-answer">
                            ระยะเวลาการเรียนรู้ขึ้นอยู่กับ:
                            <ul>
                                <li>เป้าหมายการเรียนรู้ของนักเรียน</li>
                                <li>เวลาที่คุณสามารถทุ่มเทให้กับการเรียน</li>
                            </ul>
                            โดยเฉลี่ยผู้เรียนใช้เวลา 7-12 วันในการเรียนรู้พื้นฐานและสามารถเริ่มสร้างโปรเจคได้
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>🎓 ได้รับประกาศนียบัตรหรือไม่?</span>
                            <div class="faq-toggle"></div>
                        </div>
                        <div class="faq-answer">
                            ใช่! นักเรียนจะได้รับ:
                            <ul>
                                <li>ประกาศนียบัตรหลังจากเรียนเสร็จ</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <footer class="footer">
        <div class="footer-waves">
            <div class="wave" id="wave1"></div>
            <div class="wave" id="wave2"></div>
            <div class="wave" id="wave3"></div>
            <div class="wave" id="wave4"></div>
        </div>
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-header">
                    <div class="header-icon">
                        <i class="fas fa-graduation-cap footer-icon"></i>
                        <div class="icon-ring"></div>
                    </div>
                    <h3>เกี่ยวกับเรา</h3>
                </div>
                <p>Codly - แพลตฟอร์มการเรียนรู้ออนไลน์ที่จะพาคุณก้าวสู่การเป็นนักพัฒนาที่เก่งและมั่นใจ</p>
            </div>
            <div class="footer-section">
                <div class="footer-header">
                    <div class="header-icon">
                        <i class="fas fa-comments footer-icon"></i>
                        <div class="icon-ring"></div>
                    </div>
                    <h3>ติดต่อ</h3>
                </div>
                <div class="contact-info">
                    <a href="tel:021234567" class="contact-item">
                        <ion-icon name="call-outline"></ion-icon>
                        <span>065-493-8793</span>
                    </a>
                    <a href="mailto:contact@codly.com" class="contact-item">
                        <ion-icon name="mail-outline"></ion-icon>
                        <span>19977@bdc.ac.th</span>
                    </a>
                    <div class="contact-item">
                        <ion-icon name="location-outline"></ion-icon>
                        <span>โรงเรียนพุทธชินราชพิทยา, พิษณุโลก, ประเทศไทย</span>
                    </div>
                    <div class="contact-item">
                        <ion-icon name="time-outline"></ion-icon>
                        <span>จันทร์ - ศุกร์: 9:00 - 18:00</span>
                    </div>
                </div>
            </div>
            <div class="footer-section">
                <div class="footer-header">
                    <div class="header-icon">
                        <i class="fas fa-share-alt footer-icon"></i>
                        <div class="icon-ring"></div>
                    </div>
                    <h3>ติดตามเรา</h3>
                </div>
                <div class="social-links">
                    <a href="#" class="social-btn glow-effect" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                        <div class="glow-container">
                            <div class="glow"></div>
                        </div>
                    </a>
                    <a href="#" class="social-btn glow-effect" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                        <div class="glow-container">
                            <div class="glow"></div>
                        </div>
                    </a>
                    <a href="#" class="social-btn glow-effect" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                        <div class="glow-container">
                            <div class="glow"></div>
                        </div>
                    </a>
                    <a href="#" class="social-btn glow-effect" aria-label="GitHub">
                        <i class="fab fa-github"></i>
                        <div class="glow-container">
                            <div class="glow"></div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p class="footer-note">© 2025 Codly. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">นโยบายความเป็นส่วนตัว</a>
                <a href="#">เงื่อนไขการใช้งาน</a>
                <a href="#">คำถามที่พบบ่อย</a>
            </div>
        </div>
    </footer>
    <script src="assets/js/preload.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/code-editor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navLinks = document.querySelector('.nav-links');
            const navbar = document.querySelector('.navbar');

            // Mobile menu toggle
            mobileMenuBtn.addEventListener('click', function() {
                this.classList.toggle('active');
                navLinks.classList.toggle('active');
                document.body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : '';
            });

            // Close mobile menu when clicking links
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.addEventListener('click', () => {
                    mobileMenuBtn.classList.remove('active');
                    navLinks.classList.remove('active');
                    document.body.style.overflow = '';
                });
            });

            // Navbar scroll effect
            window.addEventListener('scroll', function() {
                if (window.scrollY > 100) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        });
    </script>
</body>
</html>