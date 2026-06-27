<h5>CRUD</h5>
                        <ul>
                        <a href="<?php echo APP_ADMIN_URL; ?>"><li>Home</li></a>
                        <a href="<?php echo APP_ADMIN_URL; ?>/content-library"><li>Content Library</li></a>
                        <a href="<?php echo APP_ADMIN_URL; ?>/email-library"><li>Email Library</li></a>
                        <a href="<?php echo APP_ADMIN_URL; ?>/users"><li>Admin Users</li></a>
                        <a href="<?php echo APP_ADMIN_URL; ?>/event-logs"><li>Event Logs</li></a>
                        <a href="<?php echo APP_ADMIN_URL; ?>/waitlist"><li>Waitlist</li></a>
                        <a href="<?php echo APP_ADMIN_URL; ?>/api-tester"><li>API Tester</li></a>
                        </ul>
                        <div class="mt-2 p-2" style="position: fixed;bottom: 0;left: 0;right: 0;">
                            <button class="btn btn-primary-outline" onclick="window.location.href='<?php echo APP_ADMIN_URL; ?>/login?logout=true'"><i class="fa-solid fa-right-from-bracket"></i> Log out</button>
                        </div>
