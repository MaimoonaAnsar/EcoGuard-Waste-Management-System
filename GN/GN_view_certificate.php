```php
<?php
session_start();

include __DIR__ . '/../includes/db.php';


// =========================================================
// ACCESS CONTROL
// =========================================================
// Only Admin and GN can view certificates.

if (
    !isset($_SESSION['user_id']) ||
    !in_array((int)$_SESSION['role_id'], [2, 5], true)
) {
    header("Location: ../login.php");
    exit();
}


// =========================================================
// REQUIRED PARAMETERS
// =========================================================

if (
    !isset($_GET['user_id']) ||
    !isset($_GET['event_id'])
) {
    header("Location: GN_view_certificate.php");
    exit();
}


$user_id = (int)$_GET['user_id'];
$event_id = (int)$_GET['event_id'];


// =========================================================
// GET CERTIFICATE
// =========================================================

$stmt = $pdo->prepare("
    SELECT
        Certificate
    FROM user_participate_volunteer_event
    WHERE U_Id = ?
      AND Event_Id = ?
    LIMIT 1
");

$stmt->execute([
    $user_id,
    $event_id
]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$certificate = $row['Certificate'] ?? null;


// =========================================================
// CHECK FILE
// =========================================================

$certificateExists = false;
$certificateExtension = null;
$certificateUrl = null;

if (!empty($certificate)) {

    $certificatePath = __DIR__ . '/../' . $certificate;

    if (file_exists($certificatePath)) {

        $certificateExists = true;

        $certificateExtension = strtolower(
            pathinfo($certificatePath, PATHINFO_EXTENSION)
        );

        $certificateUrl = '../' . htmlspecialchars(
            $certificate,
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>EcoGuard | Certificate</title>

    <link
        rel="stylesheet"
        href="../css/ecoguard_responsive.css"
    >

    <style>

        /* =====================================================
           CERTIFICATE PAGE
           ===================================================== */

        .certificate-page {
            width: min(1100px, calc(100% - 40px));

            margin: 0 auto;

            padding: 40px 0 60px;
        }


        /* =====================================================
           HEADER
           ===================================================== */

        .certificate-header {
            display: flex;

            align-items: flex-end;

            justify-content: space-between;

            gap: 25px;

            margin-bottom: 25px;
        }


        .certificate-heading {
            min-width: 0;
        }


        .certificate-eyebrow {
            display: inline-block;

            margin-bottom: 7px;

            color: #5b8061;

            font-size: 12px;

            font-weight: 700;

            letter-spacing: 0.08em;

            text-transform: uppercase;
        }


        .certificate-heading h1 {
            margin: 0 0 7px;

            color: #263b2b;

            font-size: clamp(26px, 4vw, 36px);

            line-height: 1.2;
        }


        .certificate-heading p {
            margin: 0;

            color: #737b74;

            font-size: 14px;
        }


        /* =====================================================
           BACK BUTTON
           ===================================================== */

        .certificate-back {
            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            height: 40px;

            padding: 0 15px;

            border: 1px solid #d8e5d6;

            border-radius: 8px;

            background: #eef4ed;

            color: #3f7048;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;

            white-space: nowrap;

            transition: all 0.2s ease;
        }


        .certificate-back:hover {
            background: #dfeade;

            border-color: #c7d9c5;

            color: #315c39;

            transform: translateX(-2px);
        }


        /* =====================================================
           CERTIFICATE CARD
           ===================================================== */

        .certificate-container {
            background: #ffffff;

            border: 1px solid #e1e7e0;

            border-radius: 12px;

            padding: 24px;

            box-shadow:
                0 4px 20px rgba(35, 58, 40, 0.06);

            overflow: hidden;
        }


        /* =====================================================
           CERTIFICATE PREVIEW
           ===================================================== */

        .certificate-preview {
            display: flex;

            align-items: center;

            justify-content: center;

            min-height: 500px;

            padding: 20px;

            border-radius: 8px;

            background: #f7f9f6;

            border: 1px solid #e5e9e4;
        }


        .certificate-preview img {
            display: block;

            max-width: 100%;

            max-height: 750px;

            width: auto;

            height: auto;

            object-fit: contain;

            border-radius: 4px;

            box-shadow:
                0 5px 20px rgba(0, 0, 0, 0.08);
        }


        .certificate-preview iframe {
            display: block;

            width: 100%;

            height: 700px;

            border: none;

            border-radius: 4px;

            background: #ffffff;
        }


        /* =====================================================
           PDF EMBED FALLBACK
           ===================================================== */

        .certificate-pdf {
            width: 100%;

            height: 700px;

            border: none;

            border-radius: 5px;
        }


        /* =====================================================
           UNSUPPORTED / MISSING CERTIFICATE
           ===================================================== */

        .certificate-empty {
            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            min-height: 400px;

            padding: 40px;

            text-align: center;
        }


        .certificate-empty-icon {
            display: flex;

            align-items: center;

            justify-content: center;

            width: 70px;

            height: 70px;

            margin-bottom: 18px;

            border-radius: 50%;

            background: #f1f4f0;

            font-size: 30px;
        }


        .certificate-empty h2 {
            margin: 0 0 8px;

            color: #303b32;

            font-size: 20px;
        }


        .certificate-empty p {
            max-width: 450px;

            margin: 0;

            color: #747c75;

            font-size: 14px;

            line-height: 1.6;
        }


        /* =====================================================
           FOOTER ACTION
           ===================================================== */

        .certificate-actions {
            display: flex;

            justify-content: flex-end;

            gap: 10px;

            margin-top: 18px;
        }


        .action-button {
            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            min-height: 40px;

            padding: 0 15px;

            border: 1px solid #d8e5d6;

            border-radius: 8px;

            background: #ffffff;

            color: #3f7048;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;

            cursor: pointer;

            transition: all 0.2s ease;
        }


        .action-button:hover {
            background: #eef4ed;

            border-color: #c7d9c5;
        }


        /* =====================================================
           MOBILE
           ===================================================== */

        @media (max-width: 768px) {

            .certificate-page {
                width: calc(100% - 24px);

                padding: 25px 0 40px;
            }


            .certificate-header {
                align-items: flex-start;

                flex-direction: column;

                gap: 16px;
            }


            .certificate-back {
                width: auto;
            }


            .certificate-container {
                padding: 14px;
            }


            .certificate-preview {
                min-height: 350px;

                padding: 10px;
            }


            .certificate-preview iframe,
            .certificate-pdf {
                height: 600px;
            }

        }


        /* =====================================================
           SMALL MOBILE
           ===================================================== */

        @media (max-width: 480px) {

            .certificate-page {
                width: calc(100% - 20px);
            }


            .certificate-heading h1 {
                font-size: 25px;
            }


            .certificate-heading p {
                font-size: 13px;
            }


            .certificate-preview iframe,
            .certificate-pdf {
                height: 500px;
            }

        }

    </style>

</head>


<body>


<main class="certificate-page">


    <!-- =====================================================
         CERTIFICATE HEADER
         ===================================================== -->

    <section class="certificate-header">

        <div class="certificate-heading">

            <span class="certificate-eyebrow">
                EcoGuard Certificate
            </span>

            <h1>
                Volunteer Certificate
            </h1>

            <p>
                View the certificate issued for this volunteer activity.
            </p>

        </div>


        <a
            href="javascript:history.back()"
            class="certificate-back"
        >
            <span>←</span>
            Back
        </a>

    </section>



    <!-- =====================================================
         CERTIFICATE
         ===================================================== -->

    <section class="certificate-container">


        <?php if ($certificateExists): ?>


            <div class="certificate-preview">


                <?php if (
                    in_array(
                        $certificateExtension,
                        ['jpg', 'jpeg', 'png', 'webp']
                    )
                ): ?>


                    <!-- IMAGE CERTIFICATE -->

                    <img
                        src="<?= $certificateUrl ?>"
                        alt="Volunteer Certificate"
                    >


                <?php elseif ($certificateExtension === 'pdf'): ?>


                    <!-- PDF CERTIFICATE -->

                    <iframe
                        src="<?= $certificateUrl ?>"
                        title="Volunteer Certificate"
                    ></iframe>


                <?php else: ?>


                    <!-- UNSUPPORTED FORMAT -->

                    <div class="certificate-empty">

                        <div class="certificate-empty-icon">
                            📄
                        </div>

                        <h2>
                            Unsupported Certificate Format
                        </h2>

                        <p>
                            The certificate file exists, but this file
                            format cannot be displayed here.
                        </p>

                    </div>


                <?php endif; ?>


            </div>



            <!-- ACTION -->

            <?php if (
                in_array(
                    $certificateExtension,
                    ['jpg', 'jpeg', 'png', 'webp', 'pdf']
                )
            ): ?>

                <div class="certificate-actions">

                    <a
                        href="<?= $certificateUrl ?>"
                        target="_blank"
                        rel="noopener"
                        class="action-button"
                    >
                        Open Certificate ↗
                    </a>

                </div>

            <?php endif; ?>


        <?php else: ?>


            <!-- =================================================
                 NO CERTIFICATE
                 ================================================= -->

            <div class="certificate-empty">

                <div class="certificate-empty-icon">
                    📄
                </div>


                <h2>
                    Certificate Not Available
                </h2>


                <p>
                    A certificate has not been uploaded for this
                    volunteer activity, or the certificate file
                    could not be found.
                </p>

            </div>


        <?php endif; ?>


    </section>


</main>


</body>

</html>
```

### One important change

I removed:

```php
<link rel="stylesheet" href="../css/certificate_view.css">
```

because we're putting the certificate page styling **inside this same PHP file**, just like your volunteer page.

So you only need to replace **this one PHP file**.

Also, this page now has **no volunteer management UI**. Its only purpose is:

**Certificate → Preview → Open Certificate → Back**

The certificate can be either **JPG/PNG/WebP** or **PDF**.
