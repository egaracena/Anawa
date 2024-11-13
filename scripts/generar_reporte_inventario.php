<?php
session_start();
include('../config/db.php');

// Verificar si es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 1) {
    echo "Acceso denegado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formato = $_POST['formato'];

    // Consultar inventario
    $sql_inventario = "SELECT p.nomprod, p.precio, i.cantprod, i.fechactua 
                       FROM Producto p
                       JOIN Inventario i ON p.idinv = i.idinv";
    $result = $conn->query($sql_inventario);

    if ($formato == "csv") {
        // Generar CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="reporte_inventario.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, array('Producto', 'Precio', 'Cantidad', 'Última Actualización'));

        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }

        fclose($output);
    } elseif ($formato == "pdf") {
        // Generar PDF usando una biblioteca como FPDF
        require('../libs/fpdf/fpdf.php');
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);

        // Añadir encabezados
        $pdf->Cell(50, 10, 'Producto');
        $pdf->Cell(30, 10, 'Precio');
        $pdf->Cell(30, 10, 'Cantidad');
        $pdf->Cell(50, 10, 'Última Actualización');
        $pdf->Ln();

        // Añadir datos
        while ($row = $result->fetch_assoc()) {
            $pdf->Cell(50, 10, $row['nomprod']);
            $pdf->Cell(30, 10, $row['precio']);
            $pdf->Cell(30, 10, $row['cantprod']);
            $pdf->Cell(50, 10, $row['fechactua']);
            $pdf->Ln();
        }

        $pdf->Output();
    }
}

$conn->close();
?>
