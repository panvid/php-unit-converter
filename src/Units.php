<?php

declare(strict_types=1);

namespace Panvid\UnitConverter;

interface Units
{
    public const LENGTH = [1000, 1, 0.01, 0.001, 0.000001, 0.000000001];
    public const MASS = [1000000, 1000, 1, 0.001, 0.000001];
    public const TIME = [31556952, 86400, 3600, 60, 1];
    public const AMPERE = [1];
    public const TEMPERATURE = [1];
    public const LUMINOUS = [1];
    public const ENERGY = [2.77777778 * (10 ** -7), 1, 1.602176462 * (10 ** -19)];
    public const AREA = [1000000, 10000, 100, 1, 0.0001, 0.000001];
    public const FREQUENCY = [1000000000000000000000000, 1000000000000000000000, 1000000000000000000, 1000000000000000, 1000000000000, 1000000000, 1000000, 1000];
    public const SPEED = [1, 0.278, 0.514];
    public const ACCELERATION = [1];
    public const POWER = [1];
    public const VOLTAGE = [1];
    public const FORCE = [1];
    public const VOLUME = [1000000000, 1, 0.001, 0.000001];
    public const BITRATE = [1000000000, 1000000, 1000, 1];
    public const ALL = [1000000000, 100000000, 10000000, 1000000, 100000, 10000, 1000, 100, 10, 1, 0.1, 0.01, 0.001, 0.0001, 0.00001, 0.000001, 0.0000001, 0.00000001, 0.000000001];
}
