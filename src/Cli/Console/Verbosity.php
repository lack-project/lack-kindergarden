<?php

namespace Lack\Kindergarden\Cli\Console;

enum Verbosity: int {
    case NONE = 0;
    case ERROR = 1;
    case WARN = 2;
    case INFO = 3;
    case DEBUG = 4;
}
