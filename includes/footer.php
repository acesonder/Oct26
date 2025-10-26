    </div><!-- #main-content -->
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><?php echo e(APP_NAME); ?></h4>
                    <p>Trauma-informed outreach and case management platform</p>
                    <p class="version">Version <?php echo e(APP_VERSION); ?></p>
                </div>
                
                <?php if (isLoggedIn()): ?>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="/modules/client/dashboard.php">Dashboard</a></li>
                        <li><a href="/modules/messenger/index.php">Messages</a></li>
                        <li><a href="/modules/client/services.php">Services</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="/help.php">Help Center</a></li>
                        <li><a href="/privacy.php">Privacy Policy</a></li>
                        <li><a href="/terms.php">Terms of Service</a></li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <div class="footer-section">
                    <h4>Crisis Resources</h4>
                    <ul class="crisis-links">
                        <li><strong>988</strong> - Suicide & Crisis Lifeline</li>
                        <li><strong>911</strong> - Emergency Services</li>
                        <li><a href="tel:18002738255">1-800-273-8255</a> - SAMHSA Helpline</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo e(APP_NAME); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <?php if (isLoggedIn()): ?>
        <!-- Messenger Float Button -->
        <div id="messenger-float" class="messenger-float" onclick="toggleMessenger()">
            <span class="messenger-icon">💬</span>
            <span class="online-status"></span>
            <span class="unread-badge" style="display: none;">0</span>
        </div>
        
        <!-- Messenger Panel -->
        <div id="messenger-panel" class="messenger-panel" style="display: none;">
            <div class="messenger-header">
                <h3>Messages</h3>
                <button onclick="toggleMessenger()" class="close-btn">&times;</button>
            </div>
            <div class="messenger-content">
                <div id="messenger-conversations"></div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Scripts -->
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/messenger.js"></script>
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo e($js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Sound Feedback -->
    <audio id="notification-sound" src="/assets/sounds/notification.mp3" preload="auto"></audio>
    <audio id="success-sound" src="/assets/sounds/success.mp3" preload="auto"></audio>
</body>
</html>
