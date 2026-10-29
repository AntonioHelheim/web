<?php

/**
 * E3-VS1:
 * El formulario de salud ya no constituye un gate independiente.
 *
 * Los antecedentes obligatorios del perfil Usuario se validan y almacenan
 * dentro del onboarding. Este archivo se conserva como fachada de
 * compatibilidad para código histórico que todavía pueda incluirlo.
 */
require_once __DIR__ . '/repositorios/SaludRepository.php';

function healthGateIsExemptRequest(): bool
{
    return true;
}

function healthGateApplies(PDO $pdo): bool
{
    return false;
}

function healthGateBlocked(PDO $pdo): bool
{
    return false;
}

function healthGateWelcomeUrl(): string
{
    return 'bienvenida.php';
}

function healthGateMessage(): string
{
    return function_exists('t')
        ? t('health_gate_removed')
        : 'Health information is managed through onboarding and the profile editor.';
}
