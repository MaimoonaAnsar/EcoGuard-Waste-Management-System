<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

/* =========================================================
   CHECK CITIZEN LOGIN
   ========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];


/* =========================================================
   FETCH CERTIFICATES
   ========================================================= */

$stmt = $pdo->prepare("
    SELECT
        p.U_Id,
        p.Event_Id,
        p.Certificate,
        e.Name AS Event_Name,
        e.Date,
        e.Location
    FROM user_participate_volunteer_event p
    INNER JOIN volunteer_event e
        ON p.Event_Id = e.Event_Id
    WHERE p.U_Id = ?
      AND p.Certificate IS NOT NULL
      AND p.Certificate != ''
    ORDER BY e.Date DESC
");

$stmt->execute([$user_id]);

$certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EcoGuard | My Certificates</title>

    <link rel="stylesheet" href="../css/ecoguard_responsive.css">

    <style>

        /* =====================================================
           RESET
           ===================================================== */

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #f1f6ef 0%,
                    #f8faf7 50%,
                    #edf4eb 100%
                );

            color: #26382b;
        }


        /* =====================================================
           MAIN PAGE
           ===================================================== */

        .certificates-page {
            width: min(1200px, calc(100% - 40px));

            margin: 0 auto;

            padding: 35px 0 60px;
        }


        /* =====================================================
           PAGE HEADER
           ===================================================== */

        .certificates-header {
            display: flex;

            align-items: flex-start;

            justify-content: space-between;

            gap: 25px;

            margin-bottom: 30px;
        }


        .header-text {
            min-width: 0;
        }


        .eyebrow {
            display: inline-block;

            margin-bottom: 8px;

            color: #5b8061;

            font-size: 12px;

            font-weight: 800;

            letter-spacing: 0.12em;

            text-transform: uppercase;
        }


        .certificates-header h1 {
            margin: 0 0 8px;

            color: #263b2b;

            font-size: clamp(30px, 4vw, 42px);

            line-height: 1.15;
        }


        .certificates-header p {
            max-width: 650px;

            margin: 0;

            color: #6c766e;

            font-size: 15px;

            line-height: 1.6;
        }


        /* =====================================================
           DASHBOARD BUTTON
           ===================================================== */

        .dashboard-btn {
            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            flex-shrink: 0;

            min-height: 42px;

            padding: 0 17px;

            background: #ffffff;

            border: 1px solid #d5e2d3;

            border-radius: 9px;

            color: #3f7048;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;

            box-shadow:
                0 3px 12px rgba(38, 59, 43, 0.06);

            transition:
                background 0.2s ease,
                border-color 0.2s ease,
                transform 0.2s ease;
        }


        .dashboard-btn:hover {
            background: #edf5ec;

            border-color: #bfd2bd;

            transform: translateX(-2px);
        }


        .dashboard-btn .arrow {
            font-size: 17px;

            line-height: 1;
        }


        /* =====================================================
           SUMMARY
           ===================================================== */

        .summary-bar {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 24px;

            padding: 17px 20px;

            background: rgba(255, 255, 255, 0.92);

            border: 1px solid #dfe8dd;

            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(35, 58, 40, 0.05);
        }


        .summary-left {
            display: flex;

            align-items: center;

            gap: 11px;
        }


        .summary-icon {
            width: 38px;

            height: 38px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 9px;

            background: #eaf3e9;

            font-size: 18px;
        }


        .summary-text strong {
            display: block;

            color: #304534;

            font-size: 15px;
        }


        .summary-text span {
            display: block;

            margin-top: 3px;

            color: #7a837c;

            font-size: 12px;
        }


        .certificate-count {
            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 42px;

            height: 32px;

            padding: 0 12px;

            background: #3f7048;

            border-radius: 20px;

            color: #ffffff;

            font-size: 13px;

            font-weight: 800;
        }


        /* =====================================================
           CERTIFICATE GRID
           ===================================================== */

        .certificates-grid {
            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(340px, 1fr)
                );

            gap: 24px;
        }


        /* =====================================================
           CERTIFICATE CARD
           ===================================================== */

        .certificate-card {
            overflow: hidden;

            background: #ffffff;

            border: 1px solid #dfe7dd;

            border-radius: 15px;

            box-shadow:
                0 6px 22px rgba(38, 59, 43, 0.07);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }


        .certificate-card:hover {
            transform: translateY(-4px);

            box-shadow:
                0 12px 30px rgba(38, 59, 43, 0.11);
        }


        /* =====================================================
           CERTIFICATE PREVIEW
           ===================================================== */

        .certificate-preview {
            position: relative;

            width: 100%;

            height: 285px;

            display: flex;

            align-items: center;

            justify-content: center;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    #edf4eb,
                    #f8faf7
                );

            border-bottom: 1px solid #e2e8e1;
        }


        /* IMAGE CERTIFICATE */

        .certificate-preview img {
            display: block;

            width: 100%;

            height: 100%;

            object-fit: contain;

            padding: 12px;

            background: #ffffff;
        }


        /* PDF CERTIFICATE */

        .certificate-preview iframe {
            display: block;

            width: 100%;

            height: 100%;

            border: none;

            background: #ffffff;
        }


        /* =====================================================
           OTHER FILE
           ===================================================== */

        .file-preview {
            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            width: 100%;

            height: 100%;

            padding: 30px;

            text-align: center;
        }


        .file-icon {
            width: 70px;

            height: 70px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin-bottom: 12px;

            border-radius: 15px;

            background: #eaf3e9;

            font-size: 34px;
        }


        .file-preview strong {
            color: #344738;

            font-size: 15px;
        }


        .file-preview p {
            margin: 6px 0 0;

            color: #7b847c;

            font-size: 12px;
        }


        /* =====================================================
           MISSING CERTIFICATE
           ===================================================== */

        .missing-file {
            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            width: 100%;

            height: 100%;

            padding: 25px;

            text-align: center;

            color: #7c857d;
        }


        .missing-file-icon {
            width: 65px;

            height: 65px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin-bottom: 12px;

            border-radius: 50%;

            background: #f3f5f2;

            font-size: 30px;
        }


        .missing-file strong {
            color: #59635b;

            font-size: 14px;
        }


        .missing-file p {
            max-width: 240px;

            margin: 7px 0 0;

            font-size: 12px;

            line-height: 1.5;
        }


        /* =====================================================
           CARD DETAILS
           ===================================================== */

        .certificate-details {
            padding: 21px;
        }


        .certificate-title {
            margin: 0 0 15px;

            color: #293d2e;

            font-size: 18px;

            line-height: 1.4;
        }


        /* =====================================================
           INFORMATION
           ===================================================== */

        .certificate-info {
            display: flex;

            flex-direction: column;

            gap: 9px;

            margin-bottom: 20px;
        }


        .certificate-info > span {
            display: flex;

            align-items: center;

            gap: 9px;

            color: #6f7971;

            font-size: 13px;
        }


        .info-icon {
            width: 27px;

            height: 27px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

            border-radius: 7px;

            background: #f0f5ef;

            font-size: 13px;
        }


        /* =====================================================
           ACTION BUTTONS
           ===================================================== */

        .certificate-actions {
            display: flex;

            align-items: stretch;

            gap: 9px;
        }


        .view-btn {
            flex: 1;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            min-height: 41px;

            padding: 0 14px;

            background: #3f7048;

            border-radius: 8px;

            color: #ffffff;

            text-decoration: none;

            font-size: 12px;

            font-weight: 700;

            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }


        .view-btn:hover {
            background: #315c39;

            transform: translateY(-1px);
        }


        .download-btn {
            width: 44px;

            min-height: 41px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            background: #eef4ed;

            border: 1px solid #d8e5d6;

            border-radius: 8px;

            color: #3f7048;

            text-decoration: none;

            font-size: 17px;

            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }


        .download-btn:hover {
            background: #dfeade;

            transform: translateY(-1px);
        }


        /* =====================================================
           EMPTY STATE
           ===================================================== */

        .empty-certificates {
            min-height: 390px;

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            padding: 45px 25px;

            background: #ffffff;

            border: 1px solid #dfe7dd;

            border-radius: 15px;

            text-align: center;

            box-shadow:
                0 5px 20px rgba(38, 59, 43, 0.05);
        }


        .empty-icon {
            width: 82px;

            height: 82px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin-bottom: 20px;

            border-radius: 50%;

            background: #eaf3e9;

            font-size: 38px;
        }


        .empty-certificates h2 {
            margin: 0 0 9px;

            color: #304434;

            font-size: 22px;
        }


        .empty-certificates p {
            max-width: 500px;

            margin: 0;

            color: #737d75;

            font-size: 14px;

            line-height: 1.7;
        }


        /* =====================================================
           TABLET
           ===================================================== */

        @media (max-width: 768px) {

            .certificates-page {
                width: calc(100% - 24px);

                padding: 25px 0 45px;
            }


            .certificates-header {
                flex-direction: column;

                align-items: flex-start;

                gap: 18px;
            }


            .dashboard-btn {
                width: auto;
            }


            .summary-bar {
                padding: 15px;
            }


            .certificates-grid {
                grid-template-columns: 1fr;
            }

        }


        /* =====================================================
           SMALL MOBILE
           ===================================================== */

        @media (max-width: 480px) {

            .certificates-page {
                width: calc(100% - 20px);
            }


            .certificates-header h1 {
                font-size: 28px;
            }


            .certificates-header p {
                font-size: 14px;
            }


            .summary-text span {
                display: none;
            }


            .certificate-preview {
                height: 245px;
            }


            .certificate-details {
                padding: 18px;
            }

        }

    </style>

</head>


<body>


<main class="certificates-page">


    <!-- =====================================================
         HEADER
         ===================================================== -->

    <section class="certificates-header">

        <div class="header-text">

            <span class="eyebrow">
                Citizen Portal
            </span>

            <h1>
                My Certificates
            </h1>

            <p>
                View the certificates you have earned
                through your EcoGuard volunteer activities.
            </p>

        </div>


        <!-- BACK TO DASHBOARD -->

        <a
            href="citizen_dash.php"
            class="dashboard-btn"
        >

            <span class="arrow">←</span>

            Back to Dashboard

        </a>

    </section>


    <!-- =====================================================
         SUMMARY BAR
         ===================================================== -->

    <section class="summary-bar">

        <div class="summary-left">

            <div class="summary-icon">
                🏆
            </div>

            <div class="summary-text">

                <strong>
                    Your Achievements
                </strong>

                <span>
                    Certificates issued by your Grama Niladhari
                </span>

            </div>

        </div>


        <span class="certificate-count">

            <?= count($certificates) ?>

        </span>

    </section>


    <!-- =====================================================
         CERTIFICATES
         ===================================================== -->

    <?php if (!empty($certificates)): ?>

        <section class="certificates-grid">


            <?php foreach ($certificates as $cert): ?>

                <?php

                /* -------------------------------------------------
                   GET CERTIFICATE PATH
                   ------------------------------------------------- */

                $certificate = trim(
                    $cert['Certificate'] ?? ''
                );


                /*
                 * Remove accidental leading
                 * slashes/backslashes.
                 */

                $cleanPath = ltrim(
                    $certificate,
                    "/\\"
                );


                /*
                 * Physical server location.
                 */

                $serverPath =
                    __DIR__
                    . '/../'
                    . $cleanPath;


                /*
                 * Browser URL.
                 */

                $webPath =
                    '../'
                    . $cleanPath;


                /*
                 * File extension.
                 */

                $extension = strtolower(
                    pathinfo(
                        $certificate,
                        PATHINFO_EXTENSION
                    )
                );


                /*
                 * Check file.
                 */

                $fileExists =
                    !empty($cleanPath)
                    && file_exists($serverPath);

                ?>


                <!-- =================================================
                     CERTIFICATE CARD
                     ================================================= -->

                <article class="certificate-card">


                    <!-- =================================================
                         PREVIEW
                         ================================================= -->

                    <div class="certificate-preview">


                        <?php if (
                            $fileExists
                            && in_array(
                                $extension,
                                ['jpg', 'jpeg', 'png'],
                                true
                            )
                        ): ?>


                            <!-- IMAGE -->

                            <img
                                src="<?= htmlspecialchars($webPath) ?>"
                                alt="Volunteer Certificate"
                            >


                        <?php elseif (
                            $fileExists
                            && $extension === 'pdf'
                        ): ?>


                            <!-- PDF -->

                            <iframe
                                src="<?= htmlspecialchars($webPath) ?>"
                                title="Volunteer Certificate"
                            ></iframe>


                        <?php elseif ($fileExists): ?>


                            <!-- OTHER FILE -->

                            <div class="file-preview">

                                <div class="file-icon">
                                    📄
                                </div>

                                <strong>
                                    Certificate File
                                </strong>

                                <p>
                                    <?= htmlspecialchars(
                                        strtoupper($extension)
                                    ) ?>
                                    file
                                </p>

                            </div>


                        <?php else: ?>


                            <!-- MISSING FILE -->

                            <div class="missing-file">

                                <div class="missing-file-icon">
                                    ⚠️
                                </div>

                                <strong>
                                    Certificate unavailable
                                </strong>

                                <p>
                                    The certificate file could not
                                    be found on the server.
                                </p>

                            </div>


                        <?php endif; ?>


                    </div>


                    <!-- =================================================
                         DETAILS
                         ================================================= -->

                    <div class="certificate-details">


                        <h2 class="certificate-title">

                            <?= htmlspecialchars(
                                $cert['Event_Name']
                                ?? 'Volunteer Certificate'
                            ) ?>

                        </h2>


                        <div class="certificate-info">


                            <?php if (!empty($cert['Date'])): ?>

                                <span>

                                    <span class="info-icon">
                                        📅
                                    </span>

                                    <?= htmlspecialchars(
                                        $cert['Date']
                                    ) ?>

                                </span>

                            <?php endif; ?>


                            <?php if (!empty($cert['Location'])): ?>

                                <span>

                                    <span class="info-icon">
                                        📍
                                    </span>

                                    <?= htmlspecialchars(
                                        $cert['Location']
                                    ) ?>

                                </span>

                            <?php endif; ?>


                            <span>

                                <span class="info-icon">
                                    ✓
                                </span>

                                Issued by Grama Niladhari

                            </span>


                        </div>


                        <!-- =================================================
                             ACTIONS
                             ================================================= -->

                        <?php if ($fileExists): ?>

                            <div class="certificate-actions">


                                <!-- VIEW -->

                                <a
                                    href="<?= htmlspecialchars($webPath) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="view-btn"
                                >

                                    👁

                                    View Certificate

                                </a>


                                <!-- DOWNLOAD -->

                                <a
                                    href="<?= htmlspecialchars($webPath) ?>"
                                    class="download-btn"
                                    download
                                    title="Download Certificate"
                                >

                                    ⬇

                                </a>


                            </div>

                        <?php endif; ?>


                    </div>


                </article>


            <?php endforeach; ?>


        </section>


    <?php else: ?>


        <!-- =================================================
             NO CERTIFICATES
             ================================================= -->

        <section class="empty-certificates">


            <div class="empty-icon">
                🏆
            </div>


            <h2>
                No Certificates Yet
            </h2>


            <p>
                You haven't received any certificates yet.
                Once your Grama Niladhari assigns a certificate
                for one of your volunteer activities, it will
                automatically appear here.
            </p>


        </section>


    <?php endif; ?>


</main>


</body>

</html>