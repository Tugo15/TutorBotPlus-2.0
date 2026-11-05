<div class="modal fade" id="ejemplo_modal" tabindex="-1" role="dialog" aria-labelledby="ejemplo_titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="ejemplo_titulo"><i class="fa fa-book-open me-2 text-info"></i>Guía de Casos de Prueba SQL</h5>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-xs">
                <p class="text-secondary">
                    TutorBotPlus evalúa automáticamente soluciones SQL en un entorno seguro y aislado por cada caso de prueba. El sistema detecta el tipo de ejercicio según configures la <strong>Consulta de Verificación</strong>:
                </p>

                <!-- Ejemplo 1: Consulta SELECT -->
                <div class="card mb-3 border">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 text-xs font-weight-bold text-dark"><span class="badge bg-primary me-2">Caso 1</span> Consulta Directa (SELECT)</h6>
                    </div>
                    <div class="card-body py-2">
                        <p class="mb-1 text-secondary">El estudiante escribe un <code>SELECT ...</code> que produce los registros esperados.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Consulta de Verificación (Entrada):</strong>
                                <span class="d-block text-muted fst-italic">Dejar en blanco</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Salida Esperada:</strong>
                                <pre class="bg-gray-100 p-2 rounded mb-0 text-xxs font-monospace">Iron Maiden|21
Led Zeppelin|14
Deep Purple|11
Metallica|10</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ejemplo 2: Vistas (CREATE VIEW) -->
                <div class="card mb-3 border">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 text-xs font-weight-bold text-dark"><span class="badge bg-success me-2">Caso 2</span> Creación de Vista (CREATE VIEW)</h6>
                    </div>
                    <div class="card-body py-2">
                        <p class="mb-1 text-secondary">El estudiante escribe <code>CREATE VIEW vista_juegos_top AS ...;</code>.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Consulta de Verificación (Entrada):</strong>
                                <pre class="bg-gray-100 p-2 rounded mb-0 text-xxs font-monospace">SELECT titulo, ventas FROM vista_juegos_top ORDER BY ventas DESC LIMIT 3;</pre>
                            </div>
                            <div class="col-md-6">
                                <strong>Salida Esperada:</strong>
                                <pre class="bg-gray-100 p-2 rounded mb-0 text-xxs font-monospace">Elden Ring|25000000
Cyberpunk 2077|20000000
The Witcher 3|15000000</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ejemplo 3: Triggers / DDL / Procedimientos -->
                <div class="card mb-3 border">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 text-xs font-weight-bold text-dark"><span class="badge bg-warning text-dark me-2">Caso 3</span> Disparadores (Triggers) o Modificaciones</h6>
                    </div>
                    <div class="card-body py-2">
                        <p class="mb-1 text-secondary">El estudiante escribe <code>CREATE TRIGGER actualiza_saldo AFTER INSERT ON compra ...;</code>.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Consulta de Verificación (Entrada):</strong>
                                <pre class="bg-gray-100 p-2 rounded mb-0 text-xxs font-monospace">INSERT INTO compra(id, id_usuario, monto) VALUES(99, 1, 500);
SELECT saldo FROM usuario WHERE id = 1;</pre>
                            </div>
                            <div class="col-md-6">
                                <strong>Salida Esperada:</strong>
                                <pre class="bg-gray-100 p-2 rounded mb-0 text-xxs font-monospace">1500</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info py-2 px-3 text-white text-xs mb-0">
                    <i class="fa fa-check-circle me-1"></i>
                    <strong>Comparación Inteligente:</strong> El evaluador tolera variaciones menores de espacios en blanco, mayúsculas en encabezados y tipos numéricos comunes sin penalizar injustamente al estudiante.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-dark mb-0" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>
