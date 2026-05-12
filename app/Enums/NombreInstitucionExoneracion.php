<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NombreInstitucionExoneracion: string implements HasLabel
{
    case Hacienda = '01';
    case RelacionesExteriores = '02';
    case AgriculturaGanaderia = '03';
    case EconomiaIndustriaComercio = '04';
    case CruzRoja = '05';
    case Bomberos = '06';
    case ObrasEspirituSanto = '07';
    case Fecrunapa = '08';
    case Earth = '09';
    case Incae = '10';
    case Jps = '11';
    case Aresep = '12';
    case Otros = '99';

    public function getLabel(): string
    {
        return match ($this) {
            self::Hacienda => '01 - Ministerio de Hacienda',
            self::RelacionesExteriores => '02 - Ministerio de Relaciones Exteriores y Culto',
            self::AgriculturaGanaderia => '03 - Ministerio de Agricultura y Ganadería',
            self::EconomiaIndustriaComercio => '04 - Ministerio de Economía, Industria y Comercio',
            self::CruzRoja => '05 - Cruz Roja Costarricense',
            self::Bomberos => '06 - Benemérito Cuerpo de Bomberos de Costa Rica',
            self::ObrasEspirituSanto => '07 - Asociación Obras del Espíritu Santo',
            self::Fecrunapa => '08 - Federación Cruzada Nacional de protección al Anciano (Fecrunapa)',
            self::Earth => '09 - Escuela de Agricultura de la Región Húmeda (EARTH)',
            self::Incae => '10 - Instituto Centroamericano de Adm. de Empresas (INCAE)',
            self::Jps => '11 - Junta de Protección Social (JPS)',
            self::Aresep => '12 - Autoridad Reguladora de los Servicios Públicos (Aresep)',
            self::Otros => '99 - Otros',
        };
    }

    public function requiresDescription(): bool
    {
        return $this === self::Otros;
    }
}
