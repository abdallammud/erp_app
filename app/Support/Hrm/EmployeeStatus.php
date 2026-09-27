<?php

namespace App\Support\Hrm;

enum EmployeeStatus: string
{
    case Active = 'active';
    case OnLeave = 'on_leave';
    case Suspended = 'suspended';
    case Exited = 'exited';
}
