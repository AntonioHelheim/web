<!-- ============================================================
     COMPONENTE: busqueda
     Archivo:    components/busqueda/busqueda.php
     Depende de: busqueda.css, busqueda.js
     Variables PHP usadas: $patente (string)
     ============================================================ -->
<div class="row justify-content-center">
    <div class="col-sm-10 col-md-7 col-lg-5">
        <div class="search-card">

            <div class="card-header-custom">
                <h5><i class="bi bi-search"></i> Consulta por patente</h5>
            </div>

            <div class="card-body">
                <form method="GET" autocomplete="off">
                    <div class="input-group">
                        <input
                            type="text"
                            name="patente"
                            id="inputPatente"
                            class="form-control search-input"
                            placeholder="ABCD12 o AB1234"
                            value="<?= htmlspecialchars($patente ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            maxlength="6"
                            required
                            autofocus
                        >
                        <button class="btn btn-search" type="submit">
                            <i class="bi bi-search me-1"></i> Buscar
                        </button>
                    </div>
                    <p class="format-hint">
                        Formatos válidos: <span>ABCD12</span> &nbsp;|&nbsp; <span>AB1234</span>
                    </p>
                </form>
            </div>

        </div>
    </div>
</div>