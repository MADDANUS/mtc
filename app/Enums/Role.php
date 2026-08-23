<?php

namespace App\Enums;

enum Role: string
{
    case Admin        = 'admin';
    case Member       = 'member';
    case Leader       = 'leader';
    case LeaderMember = 'leader mtc';
    case Sheadprd     = 'sheadprd';
    case Sheadmtc     = 'sheadmtc';
    case Magang       = 'magang';
}
