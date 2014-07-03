<?php
/**
 * Convert the .htaccess rewrite rules into an array of pattern-replacement
 * pairs.
 * Writes src/gen-rewritemap.php
 */
$lines    = file(__DIR__ . '/../www/.htaccess');
$patterns = array();
foreach ($lines as $line) {
    if (substr($line, 0, 11) == 'RewriteRule') {
        list($n, $pattern, $replace) = explode(' ', rtrim($line));
        $patterns['#' . $pattern . '#'] = $replace;
    }
}
file_put_contents(
    __DIR__ . '/../src/gen-rewritemap.php',
    "<?php\n/* automatically created from www/.htaccess */\nreturn "
    . var_export($patterns, true)
    . ";\n?>\n"
);
?>
