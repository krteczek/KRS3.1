<?php
// test-form.php v public/
if ($_POST) {
    echo "POST DATA: ";
    print_r($_POST);
} else {
    echo '<form method="POST"><input name="test"><button>Test</button></form>';
}