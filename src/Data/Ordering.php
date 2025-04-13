<?php

namespace thgs\Functional\Data;

enum Ordering : int
{
    case LT = -1;
    case EQ = 0;
    case GT = 1;
}
