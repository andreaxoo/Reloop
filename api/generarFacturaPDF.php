<?php

require_once(__DIR__ . '/../libs/fPDF/fpdf.php');

function GenerarFacturaPDF($conn, $idPedido)
{
    $sql = "
        SELECT
            p.id_pedido,
            p.total_pedido,
            p.fecha_pedido,
            p.dir_nombre,
            p.dir_apellido,
            pr.sku,
            pr.nombre_articulo,
            dp.cantidad,
            dp.precio_unitario
        FROM Pedidos p
        INNER JOIN Detalle_Pedidos dp
            ON p.id_pedido = dp.id_pedido
        INNER JOIN Productos pr
            ON dp.id_producto = pr.id_producto
        WHERE p.id_pedido = ?
    ";

    $stmt = sqlsrv_query($conn, $sql, [$idPedido]);

    if(!$stmt){
        return false;
    }

    $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial','B',18);
    $pdf->Cell(190,10,'RE-LOOP',0,1,'C');

    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(190,10,'Comprobante de Compra',0,1,'C');

    $pdf->Ln(10);

    $primeraFila = true;

    while($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
    {
        if($primeraFila)
        {
            $pdf->SetFont('Arial','',11);

            $pdf->Cell(
                190,
                8,
                utf8_decode(
                    "Pedido #".$fila["id_pedido"]
                ),
                0,
                1
            );

            $pdf->Cell(
                190,
                8,
                utf8_decode(
                    "Cliente: ".$fila["dir_nombre"]." ".$fila["dir_apellido"]
                ),
                0,
                1
            );

            $pdf->Ln(5);

            $pdf->SetFont('Arial','B',11);

            $pdf->Cell(25,8,'SKU',1);
            $pdf->Cell(90,8,'Producto',1);
            $pdf->Cell(20,8,'Cant.',1);
            $pdf->Cell(40,8,'Precio',1);

            $pdf->Ln();

            $pdf->SetFont('Arial','',10);

            $primeraFila = false;
        }

        $pdf->Cell(25,8,$fila["sku"],1);
        $pdf->Cell(
            90,
            8,
            utf8_decode($fila["nombre_articulo"]),
            1
        );
        $pdf->Cell(20,8,$fila["cantidad"],1);
        $pdf->Cell(
            40,
            8,
            '$'.$fila["precio_unitario"],
            1
        );

        $pdf->Ln();
    }

    $pdf->Ln(10);

    $sqlTotal = "
        SELECT total_pedido
        FROM Pedidos
        WHERE id_pedido = ?
    ";

    $stmtTotal = sqlsrv_query(
        $conn,
        $sqlTotal,
        [$idPedido]
    );

    $pedido = sqlsrv_fetch_array(
        $stmtTotal,
        SQLSRV_FETCH_ASSOC
    );

    $pdf->SetFont('Arial','B',12);

    $pdf->Cell(
        190,
        10,
        'Total: $'.$pedido["total_pedido"],
        0,
        1,
        'R'
    );

    $ruta =
        __DIR__ .
        '/../facturas/factura_' .
        $idPedido .
        '.pdf';

    $pdf->Output('F', $ruta);

    return $ruta;
}