<?php session_start() ?>
<!DOCTYPE html>
<html lang="es" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bases de datos</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>
    <?php
    require '../comunes/auxiliar.php';

    const PAR_TABLA = [
        'Título' => 'titulo',
        'Año' => 'anyo',
        'Sinopsis' => 'sinopsis',
        'Duración' => 'duracion',
        'Genero' => 'genero',
    ];

    const OPC = [
        'Título' => 'titulo',
        'Año' => 'anyo',
        'Duración' => 'duracion',
        'Genero' => 'genero',
    ];

    const OPC_FIXED = [
        'Año' => 'anyo',
        'Duración' => 'duracion',
    ];

    encabezado();
    aceptaCookies();

    ?>
    <div class="container">
        <div class="row">
            <?php
            try {
                $pdo = conectar();
                comprobarParametros(['id' => ''], $_POST);
                $id = comprobarId(INPUT_POST);
                comprobarPelicula($pdo, $id);
                $pdo->beginTransaction();
                $pdo->exec('LOCK TABLE peliculas IN SHARE MODE');
                $st = $pdo->prepare('DELETE FROM peliculas WHERE id = :id');
                $st->execute([':id' => $id]);
                $_SESSION['mensaje'] = 'Película eliminada correctamente.';
                $pdo->commit();
            } catch (PermissionException|ParamException|ValidationException $e) {
                header('Location: index.php');
            } catch (EmptyParamException $e) {
                // No hago nada
            }
            mensaje();

            $buscar = isset($_GET['buscar'])? trim($_GET['buscar']): '';
            $aux = isset($_GET['opc'])? trim($_GET['opc']): '';
            $opc = OPC[array_search($aux, OPC)? array_search($aux, OPC): 'Título'];
            $where = array_search($opc, OPC_FIXED)? ":opc = $opc":"position(lower(:opc) in lower($opc)) != 0";
            $st = $pdo->prepare("SELECT p.*, genero
                                   FROM peliculas p
                                   JOIN generos g
                                     ON genero_id = g.id
                                  WHERE $where
                               ORDER BY id;");
            $st->execute([':opc' => $buscar]);
            ?>
            </div>
            <div class="row" id="busqueda">
                <div class="col-md-12">
                    <fieldset>
                        <legend>Buscar...</legend>
                        <form action="" method="get" class="form-inline">
                            <div class="form-group">
                                <label for="opc">
                                    Buscar por
                                    <select class="form-control" name="opc">
                                    <?php foreach (OPC as $titulo => $accion): ?>
                                        <option value="<?= $accion ?>" <?= selected($accion, $opc) ?> >
                                            <?= $titulo ?>
                                        </option>
                                    <?php endforeach ?>
                                    :
                                    </select>
                                </label>
                                <input id="buscar" type="text" name="buscar"
                                value="<?= h($buscar) ?>"
                                class="form-control">
                            </div>
                            <input type="submit" value="Buscar" class="btn btn-primary">
                        </form>
                    </fieldset>
                </div>
            </div>
            <hr>
            <?php pintarTabla(PAR_TABLA, $st); ?>
            <div class="row">
                <div class="text-center">
                    <a href="insertar.php" class="btn btn-info">Insertar una nueva película</a>
                </div>
            </div>
        </div>
        <?php pie(); ?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    </body>
    </html>
