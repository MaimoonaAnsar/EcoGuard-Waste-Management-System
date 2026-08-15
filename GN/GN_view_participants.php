
<?php
session_start();

include __DIR__ . '/../includes/db.php';


// =========================================================
// GN ACCESS ONLY
// =========================================================

if (
    !isset($_SESSION['user_id']) ||
    (int)$_SESSION['role_id'] !== 5
) {
    header("Location: ../login.php");
    exit();
}


// =========================================================
// EVENT FILTER
// =========================================================

$event_id = null;

if (
    isset($_GET['event_id']) &&
    is_numeric($_GET['event_id'])
) {
    $event_id = (int)$_GET['event_id'];
}


// =========================================================
// MESSAGES
// =========================================================

$success_message = '';
$error_message = '';


// =========================================================
// ASSIGN CERTIFICATE
// =========================================================

if (isset($_POST['assign_certificate'])) {

    $user_id = isset($_POST['user_id'])
        ? (int)$_POST['user_id']
        : 0;

    $post_event_id = isset($_POST['event_id'])
        ? (int)$_POST['event_id']
        : 0;


    /*
     * Make sure a citizen and event were supplied.
     */

    if ($user_id <= 0 || $post_event_id <= 0) {

        $error_message =
            "Invalid citizen or event.";

    } elseif (
        !isset($_FILES['certificate']) ||
        $_FILES['certificate']['error'] !== UPLOAD_ERR_OK
    ) {

        $error_message =
            "Please select a certificate file.";

    } else {

        $file = $_FILES['certificate'];


        // =====================================================
        // FILE SIZE
        // =====================================================

        $maxFileSize = 10 * 1024 * 1024; // 10 MB

        if ($file['size'] > $maxFileSize) {

            $error_message =
                "Certificate file must be smaller than 10 MB.";

        } else {

            // =================================================
            // ALLOWED FILE TYPES
            // =================================================

            $allowedExtensions = [
                'jpg',
                'jpeg',
                'png',
                'pdf'
            ];


            $extension = strtolower(
                pathinfo(
                    $file['name'],
                    PATHINFO_EXTENSION
                )
            );


            if (
                !in_array(
                    $extension,
                    $allowedExtensions,
                    true
                )
            ) {

                $error_message =
                    "Invalid certificate format. Please upload JPG, JPEG, PNG or PDF.";

            } else {


                // =============================================
                // VERIFY PARTICIPANT
                // =============================================

                $checkStmt = $pdo->prepare("
                    SELECT Certificate
                    FROM user_participate_volunteer_event
                    WHERE U_Id = ?
                    AND Event_Id = ?
                    LIMIT 1
                ");

                $checkStmt->execute([
                    $user_id,
                    $post_event_id
                ]);

                $participant = $checkStmt->fetch(
                    PDO::FETCH_ASSOC
                );


                if (!$participant) {

                    $error_message =
                        "This citizen is not registered for this event.";

                } else {


                    // =========================================
                    // UPLOAD DIRECTORY
                    // =========================================

                    $uploadDir =
                        __DIR__ . '/../certificates/';


                    if (!is_dir($uploadDir)) {

                        if (
                            !mkdir(
                                $uploadDir,
                                0777,
                                true
                            )
                        ) {

                            $error_message =
                                "Unable to create certificate upload folder.";
                        }
                    }


                    if ($error_message === '') {


                        // =====================================
                        // UNIQUE FILE NAME
                        // =====================================

                        $certificateName =
                            'certificate_' .
                            $post_event_id . '_' .
                            $user_id . '_' .
                            time() . '_' .
                            bin2hex(random_bytes(4)) .
                            '.' .
                            $extension;


                        $fullPath =
                            $uploadDir .
                            $certificateName;


                        // =====================================
                        // MOVE FILE
                        // =====================================

                        if (
                            move_uploaded_file(
                                $file['tmp_name'],
                                $fullPath
                            )
                        ) {


                            $databasePath =
                                'certificates/' .
                                $certificateName;


                            // =================================
                            // REMOVE OLD CERTIFICATE
                            // =================================

                            if (
                                !empty(
                                    $participant['Certificate']
                                )
                            ) {

                                $oldCertificate =
                                    __DIR__ .
                                    '/../' .
                                    $participant['Certificate'];


                                if (
                                    file_exists(
                                        $oldCertificate
                                    )
                                ) {

                                    @unlink(
                                        $oldCertificate
                                    );
                                }
                            }


                            // =================================
                            // SAVE CERTIFICATE
                            // =================================

                            $updateStmt = $pdo->prepare("
                                UPDATE user_participate_volunteer_event

                                SET Certificate = ?

                                WHERE U_Id = ?
                                AND Event_Id = ?
                            ");


                            $updateStmt->execute([
                                $databasePath,
                                $user_id,
                                $post_event_id
                            ]);


                            $success_message =
                                "Certificate assigned successfully.";

                        } else {

                            $error_message =
                                "Failed to upload the certificate.";
                        }
                    }
                }
            }
        }
    }


    /*
     * Keep the user on the same event after submitting.
     */

    $event_id = $post_event_id;
}


// =========================================================
// FETCH EVENT DETAILS
// =========================================================

$event = null;


if ($event_id !== null) {

    $eventStmt = $pdo->prepare("
        SELECT
            Event_Id,
            Name,
            Date,
            Location
        FROM volunteer_event
        WHERE Event_Id = ?
        LIMIT 1
    ");

    $eventStmt->execute([
        $event_id
    ]);

    $event = $eventStmt->fetch(
        PDO::FETCH_ASSOC
    );


    /*
     * If the event doesn't exist,
     * return to volunteer events.
     */

    if (!$event) {

        header(
            "Location: GN_view_volunteer.php"
        );

        exit();
    }
}


// =========================================================
// FETCH PARTICIPANTS
// =========================================================

if ($event_id !== null) {

    /*
     * Specific event
     */

    $stmt = $pdo->prepare("
        SELECT
            p.U_Id,
            p.Event_Id,
            p.Certificate,

            u.F_name,
            u.L_name,
            u.Email

        FROM user_participate_volunteer_event p

        INNER JOIN users u
            ON p.U_Id = u.U_Id

        WHERE p.Event_Id = ?

        ORDER BY
            u.F_name ASC,
            u.L_name ASC
    ");

    $stmt->execute([
        $event_id
    ]);

} else {

    /*
     * All volunteer participants
     */

    $stmt = $pdo->prepare("
        SELECT
            p.U_Id,
            p.Event_Id,
            p.Certificate,

            u.F_name,
            u.L_name,
            u.Email,

            e.Name AS Event_Name,
            e.Date AS Event_Date,
            e.Location AS Event_Location

        FROM user_participate_volunteer_event p

        INNER JOIN users u
            ON p.U_Id = u.U_Id

        INNER JOIN volunteer_event e
            ON p.Event_Id = e.Event_Id

        ORDER BY
            e.Date DESC,
            u.F_name ASC,
            u.L_name ASC
    ");

    $stmt->execute();
}


$participants = $stmt->fetchAll(
    PDO::FETCH_ASSOC
);


// =========================================================
// STATISTICS
// =========================================================

$totalParticipants =
    count($participants);

$issuedCertificates = 0;
$pendingCertificates = 0;


foreach ($participants as $participant) {

    if (
        !empty(
            $participant['Certificate']
        )
    ) {

        $issuedCertificates++;

    } else {

        $pendingCertificates++;
    }
}


// =========================================================
// PAGE TITLE
// =========================================================

if ($event !== null) {

    $pageTitle =
        "Event Participants";

} else {

    $pageTitle =
        "Manage Certificates";
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

    <title>
        EcoGuard |
        <?= htmlspecialchars($pageTitle) ?>
    </title>


    <link
        rel="stylesheet"
        href="../css/ecoguard_responsive.css"
    >


    <style>

        /* =====================================================
           PAGE
           ===================================================== */

        .certificate-page {

            width: min(
                1200px,
                calc(100% - 40px)
            );

            margin: 0 auto;

            padding: 40px 0 60px;
        }


        /* =====================================================
           HEADER
           ===================================================== */

        .page-heading {

            display: flex;

            justify-content: space-between;

            align-items: flex-end;

            gap: 25px;

            margin-bottom: 28px;
        }


        .eyebrow {

            display: inline-block;

            margin-bottom: 7px;

            color: #5b8061;

            font-size: 12px;

            font-weight: 700;

            letter-spacing: .08em;

            text-transform: uppercase;
        }


        .page-heading h1 {

            margin: 0 0 8px;

            color: #263b2b;

            font-size: clamp(
                28px,
                4vw,
                38px
            );

            line-height: 1.2;
        }


        .page-heading p {

            margin: 0;

            color: #6d756e;

            font-size: 14px;

            line-height: 1.5;
        }


        /* =====================================================
           EVENT DETAILS
           ===================================================== */

        .event-details {

            display: flex;

            flex-wrap: wrap;

            gap: 8px 18px;

            margin-top: 12px;

            color: #687168;

            font-size: 13px;
        }


        .event-details strong {

            color: #344637;
        }


        /* =====================================================
           NAV BUTTONS
           ===================================================== */

        .header-actions {

            display: flex;

            align-items: center;

            gap: 8px;

            flex-wrap: wrap;
        }


        .back-btn {

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

            transition: all .2s ease;
        }


        .back-btn:hover {

            background: #dfeade;

            color: #315c39;

            transform: translateY(-1px);
        }


        /* =====================================================
           STATISTICS
           ===================================================== */

        .stats-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;

            margin-bottom: 25px;
        }


        .stat-card {

            display: flex;

            align-items: center;

            gap: 13px;

            padding: 18px;

            background: #ffffff;

            border: 1px solid #e1e7e0;

            border-radius: 10px;

            box-shadow:
                0 3px 12px
                rgba(35,58,40,.04);
        }


        .stat-icon {

            display: flex;

            align-items: center;

            justify-content: center;

            width: 42px;

            height: 42px;

            flex: 0 0 42px;

            border-radius: 9px;

            background: #edf5ed;

            font-size: 18px;
        }


        .stat-number {

            display: block;

            color: #293c2d;

            font-size: 20px;

            font-weight: 700;
        }


        .stat-label {

            display: block;

            margin-top: 2px;

            color: #7a827b;

            font-size: 12px;
        }


        /* =====================================================
           TABLE
           ===================================================== */

        .table-container {

            background: #ffffff;

            border: 1px solid #e1e7e0;

            border-radius: 12px;

            overflow: hidden;

            box-shadow:
                0 4px 18px
                rgba(35,58,40,.05);
        }


        .table-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 20px 24px;

            border-bottom: 1px solid #e5e9e4;
        }


        .table-header h2 {

            margin: 0 0 4px;

            color: #293c2d;

            font-size: 19px;
        }


        .table-header p {

            margin: 0;

            color: #7a827b;

            font-size: 13px;
        }


        .table-wrapper {

            width: 100%;

            overflow-x: auto;

            -webkit-overflow-scrolling: touch;
        }


        .participants-table {

            width: 100%;

            min-width: 850px;

            border-collapse: collapse;

            text-align: left;
        }


        .participants-table thead {

            background: #f7f9f6;
        }


        .participants-table th {

            padding: 14px 18px;

            border-bottom: 1px solid #e1e7e0;

            color: #687168;

            font-size: 11px;

            font-weight: 700;

            letter-spacing: .05em;

            text-transform: uppercase;

            white-space: nowrap;
        }


        .participants-table td {

            padding: 16px 18px;

            border-bottom: 1px solid #edf0ec;

            color: #414a43;

            font-size: 13px;

            vertical-align: middle;
        }


        .participants-table tbody tr:last-child td {

            border-bottom: none;
        }


        .participants-table tbody tr {

            transition: background .15s ease;
        }


        .participants-table tbody tr:hover {

            background: #fafcf9;
        }


        /* =====================================================
           CITIZEN
           ===================================================== */

        .citizen-info {

            display: flex;

            align-items: center;

            gap: 11px;

            min-width: 200px;
        }


        .citizen-avatar {

            display: flex;

            align-items: center;

            justify-content: center;

            width: 38px;

            height: 38px;

            flex: 0 0 38px;

            border-radius: 50%;

            background: #edf5ed;

            color: #3f7048;

            font-size: 13px;

            font-weight: 700;
        }


        .citizen-name {

            display: block;

            margin-bottom: 3px;

            color: #2d3d30;

            font-weight: 700;
        }


        .citizen-email {

            display: block;

            color: #8a918b;

            font-size: 11px;
        }


        /* =====================================================
           EVENT
           ===================================================== */

        .event-name {

            display: block;

            margin-bottom: 3px;

            color: #344637;

            font-weight: 700;
        }


        .event-location {

            color: #8a918b;

            font-size: 11px;
        }


        /* =====================================================
           STATUS
           ===================================================== */

        .status {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            padding: 6px 9px;

            border-radius: 7px;

            font-size: 11px;

            font-weight: 700;

            white-space: nowrap;
        }


        .status-issued {

            background: #eaf6ec;

            color: #27733a;
        }


        .status-pending {

            background: #fff6df;

            color: #936d16;
        }


        /* =====================================================
           ACTION BUTTON
           ===================================================== */

        .action-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 6px;

            padding: 8px 12px;

            border: none;

            border-radius: 7px;

            background: #3f7048;

            color: #ffffff;

            text-decoration: none;

            font-family: inherit;

            font-size: 11px;

            font-weight: 700;

            cursor: pointer;

            white-space: nowrap;

            transition: all .2s ease;
        }


        .action-btn:hover {

            background: #315c39;

            transform: translateY(-1px);
        }


        .view-btn {

            background: #eef4ed;

            color: #3f7048;
        }


        .view-btn:hover {

            background: #dfeade;

            color: #315c39;
        }


        /* =====================================================
           UPLOAD PANEL
           ===================================================== */

        .upload-panel {

            display: none;

            margin-top: 14px;

            padding: 18px;

            background: #f8faf7;

            border: 1px solid #dfe8dc;

            border-radius: 9px;

            text-align: left;
        }


        .upload-panel.active {

            display: block;
        }


        .upload-panel h3 {

            margin: 0 0 5px;

            color: #2d3d30;

            font-size: 15px;
        }


        .upload-panel p {

            margin: 0 0 12px;

            color: #747c75;

            font-size: 12px;
        }


        .upload-panel input[type="file"] {

            width: 100%;

            box-sizing: border-box;

            padding: 8px;

            margin-bottom: 10px;

            background: #ffffff;

            border: 1px solid #d8e0d6;

            border-radius: 7px;

            font-size: 12px;
        }


        .upload-submit {

            width: 100%;

            padding: 9px 12px;

            border: none;

            border-radius: 7px;

            background: #3f7048;

            color: #ffffff;

            font-size: 12px;

            font-weight: 700;

            cursor: pointer;
        }


        .upload-submit:hover {

            background: #315c39;
        }


        /* =====================================================
           MESSAGES
           ===================================================== */

        .message {

            margin-bottom: 20px;

            padding: 12px 15px;

            border-radius: 8px;

            font-size: 13px;

            font-weight: 600;
        }


        .success-message {

            background: #eaf6ec;

            color: #27733a;

            border: 1px solid #cde6d1;
        }


        .error-message {

            background: #fff0f0;

            color: #a13b3b;

            border: 1px solid #f0cccc;
        }


        /* =====================================================
           EMPTY STATE
           ===================================================== */

        .empty-state {

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            min-height: 330px;

            padding: 40px;

            background: #ffffff;

            border: 1px solid #e1e7e0;

            border-radius: 12px;

            text-align: center;
        }


        .empty-icon {

            display: flex;

            align-items: center;

            justify-content: center;

            width: 62px;

            height: 62px;

            margin-bottom: 16px;

            border-radius: 50%;

            background: #edf5ed;

            font-size: 26px;
        }


        .empty-state h2 {

            margin: 0 0 8px;

            color: #2d3d30;

            font-size: 20px;
        }


        .empty-state p {

            margin: 0;

            color: #747c75;

            font-size: 13px;
        }


        /* =====================================================
           MOBILE
           ===================================================== */

        @media (max-width: 768px) {

            .certificate-page {

                width: calc(100% - 24px);

                padding: 25px 0 40px;
            }


            .page-heading {

                align-items: flex-start;

                flex-direction: column;

                gap: 16px;
            }


            .header-actions {

                width: 100%;
            }


            .back-btn {

                width: auto;
            }


            .stats-grid {

                grid-template-columns: 1fr;
            }


            .table-container {

                border-radius: 10px;
            }


            .table-header {

                padding: 18px;
            }


            .participants-table {

                min-width: 850px;
            }

        }


        @media (max-width: 480px) {

            .certificate-page {

                width: calc(100% - 20px);
            }


            .page-heading h1 {

                font-size: 25px;
            }


            .event-details {

                flex-direction: column;

                gap: 5px;
            }

        }

    </style>

</head>


<body>


<?php include 'gn_header.php'; ?>


<main class="certificate-page">


    <!-- =====================================================
         PAGE HEADER
         ===================================================== -->

    <section class="page-heading">

        <div>

            <span class="eyebrow">
                Grama Niladhari
            </span>


            <h1>
                <?= htmlspecialchars($pageTitle) ?>
            </h1>


            <?php if ($event): ?>

                <p>
                    Manage certificates for citizens who participated
                    in this volunteer event.
                </p>


                <div class="event-details">

                    <span>
                        <strong>Event:</strong>
                        <?= htmlspecialchars($event['Name']) ?>
                    </span>

                    <span>
                        📅 <?= htmlspecialchars($event['Date']) ?>
                    </span>

                    <span>
                        📍 <?= htmlspecialchars($event['Location']) ?>
                    </span>

                </div>

            <?php else: ?>

                <p>
                    Assign and manage certificates for volunteer participants.
                </p>

            <?php endif; ?>

        </div>


        <div class="header-actions">


            <?php if ($event): ?>

                <a
                    href="GN_view_volunteer.php"
                    class="back-btn"
                >
                    ← Volunteer Events
                </a>

            <?php endif; ?>


            <a
                href="gn_dash.php"
                class="back-btn"
            >
                Dashboard
            </a>


        </div>

    </section>



    <!-- =====================================================
         MESSAGES
         ===================================================== -->

    <?php if ($success_message): ?>

        <div class="message success-message">

            ✓
            <?= htmlspecialchars($success_message) ?>

        </div>

    <?php endif; ?>


    <?php if ($error_message): ?>

        <div class="message error-message">

            ✕
            <?= htmlspecialchars($error_message) ?>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         STATISTICS
         ===================================================== -->

    <section class="stats-grid">


        <div class="stat-card">

            <div class="stat-icon">
                👥
            </div>

            <div>

                <span class="stat-number">
                    <?= $totalParticipants ?>
                </span>

                <span class="stat-label">
                    Participants
                </span>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ✓
            </div>

            <div>

                <span class="stat-number">
                    <?= $issuedCertificates ?>
                </span>

                <span class="stat-label">
                    Certificates Issued
                </span>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ⏳
            </div>

            <div>

                <span class="stat-number">
                    <?= $pendingCertificates ?>
                </span>

                <span class="stat-label">
                    Pending Certificates
                </span>

            </div>

        </div>


    </section>



    <!-- =====================================================
         PARTICIPANTS
         ===================================================== -->

    <?php if (!empty($participants)): ?>


        <section class="table-container">


            <div class="table-header">

                <div>

                    <h2>
                        Volunteer Participants
                    </h2>

                    <p>
                        Select a citizen to assign their certificate.
                    </p>

                </div>

            </div>



            <div class="table-wrapper">


                <table class="participants-table">


                    <thead>

                        <tr>

                            <th>
                                Citizen
                            </th>

                            <?php if (!$event): ?>

                                <th>
                                    Event
                                </th>

                            <?php endif; ?>


                            <th>
                                Date
                            </th>

                            <th>
                                Certificate
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach ($participants as $participant): ?>


                        <?php

                        $fullName = trim(
                            ($participant['F_name'] ?? '') .
                            ' ' .
                            ($participant['L_name'] ?? '')
                        );


                        $initial = strtoupper(
                            substr(
                                $participant['F_name'] ?? 'C',
                                0,
                                1
                            )
                        );


                        $hasCertificate =
                            !empty(
                                $participant['Certificate']
                            );


                        $rowEventName =
                            $event
                                ? $event['Name']
                                : ($participant['Event_Name'] ?? '');


                        $rowEventDate =
                            $event
                                ? $event['Date']
                                : ($participant['Event_Date'] ?? '');

                        ?>


                        <tr>


                            <!-- CITIZEN -->

                            <td>

                                <div class="citizen-info">

                                    <div class="citizen-avatar">

                                        <?= htmlspecialchars(
                                            $initial
                                        ) ?>

                                    </div>


                                    <div>

                                        <span class="citizen-name">

                                            <?= htmlspecialchars(
                                                $fullName
                                            ) ?>

                                        </span>


                                        <span class="citizen-email">

                                            <?= htmlspecialchars(
                                                $participant['Email'] ?? ''
                                            ) ?>

                                        </span>

                                    </div>

                                </div>

                            </td>



                            <!-- EVENT -->

                            <?php if (!$event): ?>

                                <td>

                                    <span class="event-name">

                                        <?= htmlspecialchars(
                                            $rowEventName
                                        ) ?>

                                    </span>


                                    <span class="event-location">

                                        📍

                                        <?= htmlspecialchars(
                                            $participant['Event_Location'] ?? ''
                                        ) ?>

                                    </span>

                                </td>

                            <?php endif; ?>



                            <!-- DATE -->

                            <td>

                                📅

                                <?= htmlspecialchars(
                                    $rowEventDate
                                ) ?>

                            </td>



                            <!-- STATUS -->

                            <td>

                                <?php if ($hasCertificate): ?>

                                    <span
                                        class="status status-issued"
                                    >
                                        ✓ Issued
                                    </span>

                                <?php else: ?>

                                    <span
                                        class="status status-pending"
                                    >
                                        ⏳ Pending
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- ACTION -->

                            <td>


                                <?php if ($hasCertificate): ?>


                                    <a
                                        href="certificate_view.php?user_id=<?= (int)$participant['U_Id'] ?>&event_id=<?= (int)$participant['Event_Id'] ?>"
                                        class="action-btn view-btn"
                                    >
                                        View Certificate →
                                    </a>


                                <?php else: ?>


                                    <button
                                        type="button"
                                        class="action-btn"
                                        onclick="toggleUpload(
                                            'upload-<?= (int)$participant['U_Id'] ?>-<?= (int)$participant['Event_Id'] ?>'
                                        )"
                                    >
                                        Assign Certificate
                                    </button>


                                <?php endif; ?>



                                <!-- =================================
                                     UPLOAD FORM
                                     ================================= -->

                                <?php if (!$hasCertificate): ?>


                                    <div
                                        id="upload-<?= (int)$participant['U_Id'] ?>-<?= (int)$participant['Event_Id'] ?>"
                                        class="upload-panel"
                                    >


                                        <h3>
                                            Assign Certificate
                                        </h3>


                                        <p>

                                            Upload the certificate
                                            for
                                            <strong>
                                                <?= htmlspecialchars(
                                                    $fullName
                                                ) ?>
                                            </strong>.

                                        </p>


                                        <form
                                            method="POST"
                                            enctype="multipart/form-data"
                                        >


                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= (int)$participant['U_Id'] ?>"
                                            >


                                            <input
                                                type="hidden"
                                                name="event_id"
                                                value="<?= (int)$participant['Event_Id'] ?>"
                                            >


                                            <input
                                                type="file"
                                                name="certificate"
                                                accept=".jpg,.jpeg,.png,.pdf"
                                                required
                                            >


                                            <button
                                                type="submit"
                                                name="assign_certificate"
                                                class="upload-submit"
                                            >
                                                Assign Certificate
                                            </button>


                                        </form>


                                    </div>


                                <?php endif; ?>


                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>

                </table>

            </div>

        </section>


    <?php else: ?>


        <section class="empty-state">

            <div class="empty-icon">
                👥
            </div>


            <h2>
                No Participants Found
            </h2>


            <p>

                No citizens have volunteered
                for this event yet.

            </p>

        </section>


    <?php endif; ?>


</main>


<?php include 'gn_footer.php'; ?>


<script>

function toggleUpload(id) {

    const panel =
        document.getElementById(id);

    if (!panel) {
        return;
    }


    const isOpen =
        panel.classList.contains('active');


    /*
     * Close all other upload panels
     */

    document
        .querySelectorAll('.upload-panel')
        .forEach(function(item) {

            item.classList.remove('active');

        });


    /*
     * Open selected panel
     */

    if (!isOpen) {

        panel.classList.add('active');

        panel.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

    }

}

</script>


</body>

</html>

