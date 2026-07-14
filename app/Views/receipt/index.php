<?php
    use App\Controllers\PaymentsController;
    use chillerlan\QRCode\QRCode;
    use chillerlan\QRCode\QROptions;
    use chillerlan\QRCode\Output\QRGdImagePNG;

    if(isset($_GET['modal']) && $_GET['modal'] == 1) {
        $modal = true;
        $id = $_GET['id'];
    } else {
        $modal = false;
    }
    
    $paymentsController = new PaymentsController();
    $payment = json_decode($paymentsController->view($id));

    $data = json_encode([
        'type' => 'receipt',
        'uuid' => $payment->data->id
    ], JSON_UNESCAPED_SLASHES);

    $options = new QROptions([
        'outputInterface' => QRGdImagePNG::class,
        'scale'           => 6,
    ]);

    $qr = (new QRCode($options))->render($data);

    $title = "Recibo de Pago";
    $section = "Recibo de Pago";

    if(!$modal)
        require_once __DIR__.'/../layout/title.php';
    require_once __DIR__.'/receipt.php';
?>

<script>
    var currentLink = '<?= base_url('payments'); ?>';
</script>