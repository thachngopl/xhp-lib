<?hh
/*
 *  Copyright (c) 2015, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

// Convert the composer-generated classmap into something usable without
// Composer.

namespace Facebook\XHPLib\Autoloader;

function generate() {
  $root_dir = dirname(__DIR__);
  $src_dir = $root_dir.'/src';
  $in_file = $root_dir.'/vendor/composer/autoload_classmap.php';

  $classmap = new Map(require($in_file));
  $classmap = $classmap
    ->filter($path ==> strpos($path, $src_dir) === 0)
    ->map($path ==> substr($path, strlen($root_dir)));

  // $classmap ~= Map { 'xhp_foo' => '/src/core/Foo.php' };
  $classmap_source = str_replace('HH\Map', 'Map', var_export($classmap, true));
  $classmap_source = str_replace("\n", "\n  ", $classmap_source);

 echo <<<EOF
<?hh
/** THIS FILE IS AUTOMATICALLY GENERATED - DO NOT EDIT BY HAND **
 *
 * If you are using Composer, ignore this file.
 *
 * If you are not using Composer, you can use this function directly, but you
 * probably just want to include init.php instead.
 */

namespace Facebook\XHPLib\Autoloader;

function autoload(\$class): bool {
  \$classmap = $classmap_source;

  if (\$classmap->containsKey(\$class)) {
    require_once(__DIR__.\$classmap[\$class]);
    return class_exists(\$class, /* autoload = */ false);
  }
  return false;
}
EOF;
}

generate();
