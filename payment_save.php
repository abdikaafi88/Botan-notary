<?php
require_once __DIR__.'/config/auth.php';
require_admin();
http_response_code(410);
exit('Standalone payment saving is no longer available. Use the archive editor.');
