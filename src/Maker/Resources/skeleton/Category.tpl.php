<?= "<?php\n" ?>

namespace <?= $namespace ?>;

<?= $use_statements ?>

enum <?= $class_name ?> :int implements CategoryInterface 
{
    case FIRST_CATEGORY = 1;
    case SECOND_CATEGORY = 2;
    case THIRD_CATEGORY = 3;
}
