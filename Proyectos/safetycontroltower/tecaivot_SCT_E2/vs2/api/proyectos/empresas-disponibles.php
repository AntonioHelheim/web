<?php
/**
 * GET /api/proyectos/empresas-disponibles.php
 *
 * Lista las empresas activas, para el selector que ven los roles admin
 * en Gestión de Proyectos. Solo lectura, alcance acotado a este módulo.
 *
 * A propósito NO reutiliza api/empresas/listar.php: ese endpoint exige
 * el rol exacto 'administrador_completo' (ver empresasIsGlobalAdmin en
 * api/empresas/common.php), mientras que acá se acepta también
 * 'administrador' — que es el rol que efectivamente tienen hoy las
 * cuentas admin ya creadas (ver Registro de cambios de alcance /
 * dump SQL). Reutilizar ese endpoint tal cual habría dejado este
 * selector inaccesible para esas cuentas.
 */

require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

requireRole($pdo, ['administrador', 'administrador_completo']);

try {
    $empresas = empresaListar($pdo);
    responderJSON(true, $empresas);
} catch (PDOException $e) {
    error_log('api/proyectos/empresas-disponibles.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudieron obtener las empresas.', 500);
}
