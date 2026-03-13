<!-- ============================================================
     COMPONENTE: resultados
     Archivo:    components/resultados/resultados.php
     Depende de: resultados.css
     Variables PHP usadas: $searched, $error, $patente, $results, formatearFecha()
     ============================================================ -->
<?php if ($searched): ?>
<div class="row justify-content-center mt-4 results-wrapper">
    <div class="col-12 col-xl-11">

        <?php if ($error): ?>
            <!-- ── Error de validación ─────────────── -->
            <div class="alert-helheim-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?= htmlspecialchars($error) ?>
            </div>

        <?php else: ?>

            <!-- ── Meta: patente + conteo ──────────── -->
            <div class="results-meta">
                <span>Patente:</span>
                <strong><?= htmlspecialchars($patente) ?></strong>
                <span class="results-count <?= count($results) > 0 ? 'has-results' : '' ?>">
                    <?= count($results) ?> registro<?= count($results) !== 1 ? 's' : '' ?>
                </span>
            </div>

            <!-- ── Tabla Desktop (≥768px) ───────────── -->
            <div class="results-table-wrapper table-responsive d-none d-md-block">
                <table class="table table-helheim">
                    <thead>
                        <tr>
                            <th><i class="bi bi-card-text me-1"></i> Patente</th>
                            <th><i class="bi bi-calendar3 me-1"></i> Fecha Ejecución</th>
                            <th><i class="bi bi-upc me-1"></i> VIN</th>
                            <th><i class="bi bi-cpu me-1"></i> ThinkCar</th>
                            <th><i class="bi bi-clipboard-check me-1"></i> Conclusión Técnica</th>
                            <th><i class="bi bi-person me-1"></i> Propietario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($results)): ?>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td>
                                        <span class="badge-patente">
                                            <?= htmlspecialchars($row['field_279']) ?>
                                        </span>
                                    </td>
                                    <td class="fecha-cell">
                                        <?= htmlspecialchars(formatearFecha($row['field_280'])) ?>
                                    </td>
                                    <td style="font-family:var(--font-cond); letter-spacing:.06em;">
                                        <?= htmlspecialchars($row['field_281']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['field_282']) ?></td>
                                    <td>
                                        <div class="conclusion-cell">
                                            <i class="bi bi-check-circle-fill"></i>
                                            <span><?= htmlspecialchars($row['field_327']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row['field_331']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="bi bi-car-front"></i>
                                        <p>No existen registros en nuestro sistema para esta patente.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ── Cards Móvil (<768px) ─────────────── -->
            <div class="mobile-results d-md-none">
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $i => $row): ?>
                        <div class="result-card" style="animation-delay:<?= $i * 0.07 ?>s">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="result-card-label">Patente</div>
                                    <div class="result-card-value">
                                        <span class="badge-patente"><?= htmlspecialchars($row['field_279']) ?></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="result-card-label">Propietario</div>
                                    <div class="result-card-value"><?= htmlspecialchars($row['field_331']) ?: '-' ?></div>
                                </div>
                                <div class="col-12">
                                    <div class="result-card-label">Fecha Ejecución</div>
                                    <div class="result-card-value fecha-cell" style="font-size:.83rem;">
                                        <?= htmlspecialchars(formatearFecha($row['field_280'])) ?>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="result-card-label">VIN</div>
                                    <div class="result-card-value" style="font-family:var(--font-cond); letter-spacing:.06em; word-break:break-all;">
                                        <?= htmlspecialchars($row['field_281']) ?: '-' ?>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="result-card-label">ThinkCar</div>
                                    <div class="result-card-value"><?= htmlspecialchars($row['field_282']) ?: '-' ?></div>
                                </div>
                                <div class="col-12">
                                    <div class="result-card-label">Conclusión Técnica</div>
                                    <div class="result-card-value">
                                        <i class="bi bi-check-circle-fill text-accent me-1" style="font-size:.8rem;"></i>
                                        <?= htmlspecialchars($row['field_327']) ?: '-' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="result-card text-center">
                        <i class="bi bi-car-front d-block mb-2" style="font-size:2rem; color:var(--clr-border);"></i>
                        <p class="mb-0" style="font-family:var(--font-cond); color:var(--clr-muted); letter-spacing:.06em; font-size:.9rem;">
                            No existen registros para esta patente.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
</div>
<?php endif; ?>