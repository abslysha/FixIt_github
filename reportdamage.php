<?php
require 'auth_check.php';
$initial = strtoupper(substr($_SESSION['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixIt - Report Damage</title>
 
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
 
    </head>
 
<body>
 
    <aside class="sidebar">
 
        <div class="sidebar-logo">
            <img src="FixIt_Logo.png" alt="FixIt Logo">
            
        </div>
 
        <button class="sidebar-menu-btn">
            <span></span>
            <span></span>
            <span></span>
        </button>
 
        <nav>
 
            <a href="userdb.php" class="nav-item">
                Dashboard
            </a>
 
            <a href="reportdamage.php" class="nav-item active">
                Report Damage
            </a>
            <a href="myreport.php" class="nav-item">
                My Report
            </a>
 
            <a href="trackstatus.php" class="nav-item">
                Track Status
            </a>
 
        </nav>
 
        <div class="sidebar-spacer"></div>
 
        <a href="login.php" class="nav-item">
            Logout
        </a>
 
    </aside>
 
    <main class="main">
 
        <header class="topbar">
 
            <h1 class="topbar-title">
                REPORT DAMAGE
            </h1>
 
            <div class="topbar-actions">
 
                <button class="icon-btn">
                    🔔
                    <span class="notif-dot"></span>
                </button>
 
                <div class="avatar">
                    <?php echo htmlspecialchars($initial); ?>
                </div>
 
            </div>
 
        </header>
 
        <?php if (isset($_GET['error'])): ?>
            <p style="color:#e53e3e; margin-bottom:16px;">Something went wrong submitting your report. Please try again.</p>
        <?php endif; ?>
 
        <div class="report-page">
 
            <div class="report-form-wrapper">
 
                <h2 class="report-heading">REPORT DAMAGE</h2>
                <p class="report-subtext">Please fill out the form below to report a damage issue.</p>
 
                <form action="submit_report.php" method="POST" enctype="multipart/form-data" id="reportForm">
                <div class="report-form-card">
 
                    <div class="form-group">
                        <label>Issue Title</label>
                        <input type="text" name="title" placeholder="Enter issue title." required>
                    </div>
 
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" placeholder="Describe the issue in detail..." required></textarea>
                    </div>
 
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" placeholder="Enter location." required>
                    </div>
 
                    <div class="form-group">
                        <label>Upload Photo (Optional)</label>
 
                        <!-- Default upload prompt (hidden once a file is selected) -->
                        <div class="upload-area" id="uploadArea">
                            <div class="upload-icon">⬆</div>
                            <p>select your file or drag and drop</p>
                            <small>png, pdf, jpg, docx accepted</small>
                            <button type="button" class="browse-btn" id="browseBtn">browse</button>
 
                            <input type="file" name="photo" id="fileInput" accept=".png,.jpg,.jpeg,.pdf,.docx" hidden>
                        </div>
 
                        <div id="previewBox" style="display:none; position:relative; border-radius:14px; overflow:hidden; border:1px solid #c5cedf;">
                            <img id="imagePreview" src="" alt="Preview" style="display:block; width:100%; max-height:340px; object-fit:cover;">
 
                            <div id="filePreviewCard" style="display:none; padding:36px 20px; text-align:center; background:#dce3f5;">
                                <div style="font-size:42px; margin-bottom:8px;">📄</div>
                                <p id="filePreviewName" style="font-family:'Courier New',monospace; font-size:13px; color:var(--text);"></p>
                            </div>
 
                            <button type="button" id="removeFileBtn" title="Remove file" style="position:absolute; top:10px; right:10px; width:30px; height:30px; border:none; border-radius:50%; background:rgba(0,0,0,0.55); color:white; font-size:16px; cursor:pointer; display:flex; align-items:center; justify-content:center;">✕</button>
 
                            <div style="background:white; padding:10px 14px;">
                                <p id="fileName" style="font-size:12px; color:#7a869a; font-family:'Courier New',monospace; margin:0;"></p>
                            </div>
                        </div>
                    </div>
 
                    <div class="form-actions">
                        <button class="btn-cancel" type="reset" id="cancelBtn">CANCEL</button>
                        <button class="btn-submit" type="submit">SUBMIT REPORT</button>
                    </div>
 
                </div>
                </form>
            </div>
 
            <div class="report-illustration">
                <div class="warning-icon">⚠️</div>
                <img src="fixIllusion.png" alt="Report illustration" onerror="this.style.display='none'">
            </div>
 
        </div>
 
    </main>
 
    <script>
        const uploadArea     = document.getElementById('uploadArea');
        const browseBtn      = document.getElementById('browseBtn');
        const fileInput       = document.getElementById('fileInput');
        const fileNameText    = document.getElementById('fileName');
        const previewBox      = document.getElementById('previewBox');
        const imagePreview    = document.getElementById('imagePreview');
        const filePreviewCard = document.getElementById('filePreviewCard');
        const filePreviewName = document.getElementById('filePreviewName');
        const removeFileBtn   = document.getElementById('removeFileBtn');
        const cancelBtn       = document.getElementById('cancelBtn');
 
        browseBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.click();
        });
 
        uploadArea.addEventListener('click', (e) => {
            if (e.target === browseBtn) return;
            fileInput.click();
        });
 
        fileInput.addEventListener('change', () => {
            handleFile(fileInput.files[0]);
        });
 
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#2ab5b5';
        });
 
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = '#a0aec0';
        });
 
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#a0aec0';
 
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                handleFile(e.dataTransfer.files[0]);
            }
        });
 
        function handleFile(file) {
            if (!file) return;
 
            const allowed = ['png', 'jpg', 'jpeg', 'pdf', 'docx'];
            const ext = file.name.split('.').pop().toLowerCase();
 
            if (!allowed.includes(ext)) {
                alert(`"${file.name}" is not a supported file type.`);
                fileInput.value = '';
                return;
            }
 
            fileNameText.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
 
            uploadArea.style.display = 'none';
            previewBox.style.display = 'block';
 
            if (['png', 'jpg', 'jpeg'].includes(ext)) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    filePreviewCard.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
                filePreviewCard.style.display = 'block';
                filePreviewName.textContent = file.name;
            }
        }
 
        function resetUpload() {
            fileInput.value = '';
            imagePreview.src = '';
            fileNameText.textContent = '';
            previewBox.style.display = 'none';
            uploadArea.style.display = 'flex';
            uploadArea.style.borderColor = '#a0aec0';
        }
 
        removeFileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            resetUpload();
        });
 
        cancelBtn.addEventListener('click', resetUpload);
    </script>
 
</body>
</html>