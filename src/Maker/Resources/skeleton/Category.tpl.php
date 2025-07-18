<?= "<?php\n" ?>

namespace <?= $namespace ?>;

<?= $use_statements ?>

enum <?= $class_name ?> :int implements CategoryInterface 
{
    case FOO = 1;
    case BAR = 2;
}
