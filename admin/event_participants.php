<?php
session_start();

include __DIR__ . '/../includes/db.php';


/* =========================================================
   ADMIN ACCESS ONLY
========================================================= */

if (!isset($_SESSION['user_id']) || (int)$_SESSION['role_id'] !== 2) {
    header("Location: ../login.php");
    exit();
}


/* =========================================================
   CHECK EVENT ID
========================================================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_volunteer_events.php");
    exit();
}

$event_id = (int)$_GET['id'];


/* =========================================================
   FETCH EVENT
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        Event_Id,
        Name,
        Date,
        Location
    FROM volunteer_event
    WHERE Event_Id = ?
");

$stmt->execute([$event_id]);

$event = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$event) {
    header("Location: view_volunteer_events.php");
    exit();
}


/* =========================================================
   FETCH PARTICIPANTS
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        u.U_Id,
        u.F_name,
        u.L_name,
        u.Email,
        p.Proof_Image,
        p.Certificate
    FROM user_participate_volunteer_event p

    INNER JOIN users u
        ON p.U_Id = u.U_Id

    WHERE p.Event_Id = ?

    ORDER BY
        u.F_name ASC,
        u.L_name ASC
");

$stmt->execute([$event_id]);

$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        EcoGuard | Event Participants
    </title>


    <link
        rel="stylesheet"
        href="../css/ecoguard_responsive.css"
    >


    <style>

        /* =====================================================
           PAGE
        ===================================================== */

        .participants-page {
            width: min(1200px, calc(100% - 40px));

            margin: 0 auto;

            padding: 40px 0 60px;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .page-heading {

            display: flex;

            align-items: flex-end;

            justify-content: space-between;

            gap: 25px;

            margin-bottom: 30px;
        }


        .eyebrow {

            display: inline-block;

            margin-bottom: 8px;

            color: #5b8061;

            font-size: 13px;

            font-weight: 700;

            letter-spacing: .08em;

            text-transform: uppercase;
        }


        .page-heading h1 {

            margin: 0 0 10px;

            color: #263b2b;

            font-size: clamp(28px, 4vw, 38px);
        }


        .event-details {

            display: flex;

            flex-wrap: wrap;

            gap: 8px 18px;

            margin: 0;

            color: #6d756e;

            font-size: 14px;
        }


        /* =====================================================
           BACK BUTTON
        ===================================================== */

        .back-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 10px 16px;

            border: 1px solid #d8e5d6;

            border-radius: 8px;

            background: #eef4ed;

            color: #3f7048;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;

            white-space: nowrap;

            transition: .2s ease;
        }


        .back-btn:hover {

            background: #dfeade;

            transform: translateX(-2px);
        }


        /* =====================================================
           PARTICIPANTS CONTAINER
        ===================================================== */

        .participants-container {

            background: #ffffff;

            border: 1px solid #e1e7e0;

            border-radius: 12px;

            overflow: hidden;

            box-shadow: 0 4px 18px rgba(35,58,40,.05);
        }


        .section-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            padding: 22px 24px;

            border-bottom: 1px solid #e5e9e4;
        }


        .section-header h2 {

            margin: 0 0 4px;

            color: #293c2d;

            font-size: 19px;
        }


        .section-header p {

            margin: 0;

            color: #7a827b;

            font-size: 13px;
        }


        .count-badge {

            padding: 7px 11px;

            border-radius: 7px;

            background: #f1f6f1;

            color: #3f6547;

            font-size: 12px;

            font-weight: 700;

            white-space: nowrap;
        }


        /* =====================================================
           TABLE
        ===================================================== */

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

            padding: 14px 20px;

            border-bottom: 1px solid #e1e7e0;

            color: #687168;

            font-size: 12px;

            font-weight: 700;

            letter-spacing: .04em;

            text-transform: uppercase;

            white-space: nowrap;
        }


        .participants-table td {

            padding: 16px 20px;

            border-bottom: 1px solid #edf0ec;

            color: #414a43;

            font-size: 14px;

            vertical-align: middle;
        }


        .participants-table tbody tr:last-child td {

            border-bottom: none;
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

            gap: 12px;

            min-width: 220px;
        }


        .citizen-avatar {

            width: 40px;

            height: 40px;

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

            border-radius: 50%;

            background: #edf5ed;

            color: #3f7048;

            font-size: 15px;

            font-weight: 700;
        }


        .citizen-name strong {

            display: block;

            margin-bottom: 3px;

            color: #2d3d30;

            font-size: 14px;
        }


        .citizen-name span {

            color: #8a918b;

            font-size: 12px;
        }


        /* =====================================================
           PROOF
        ===================================================== */

        .proof-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 8px 12px;

            border-radius: 7px;

            background: #eef4ed;

            color: #3f7048;

            text-decoration: none;

            font-size: 12px;

            font-weight: 600;

            white-space: nowrap;
        }


        .proof-btn:hover {

            background: #dfeade;
        }


        .not-available {

            color: #9a9f9b;

            font-size: 12px;
        }


        /* =====================================================
           CERTIFICATE STATUS
        ===================================================== */

        .certificate-status {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding: 7px 10px;

            border-radius: 7px;

            font-size: 12px;

            font-weight: 700;

            white-space: nowrap;
        }


        .certificate-issued {

            background: #eaf7ed;

            color: #26733a;
        }


        .certificate-pending {

            background: #f5f5f3;

            color: #777d78;
        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .empty-state {

            min-height: 330px;

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            padding: 40px;

            background: #ffffff;

            border: 1px solid #e1e7e0;

            border-radius: 12px;

            text-align: center;
        }


        .empty-icon {

            width: 65px;

            height: 65px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin-bottom: 18px;

            border-radius: 50%;

            background: #edf5ed;

            font-size: 28px;
        }


        .empty-state h2 {

            margin: 0 0 8px;

            color: #2d3d30;

            font-size: 21px;
        }


        .empty-state p {

            margin: 0;

            color: #747c75;

            font-size: 14px;
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 768px) {

            .participants-page {

                width: calc(100% - 24px);

                padding: 25px 0 40px;
            }


            .page-heading {

                align-items: flex-start;

                flex-direction: column;

                gap: 18px;
            }


            .back-btn {

                width: auto;
            }


            .section-header {

                padding: 18px;
            }


            .participants-table {

                min-width: 800px;
            }


            .participants-table th,
            .participants-table td {

                padding: 14px 15px;
            }

        }


        @media (max-width: 480px) {

            .participants-page {

                width: calc(100% - 20px);
            }


            .page-heading h1 {

                font-size: 26px;
            }

        }

    </style>

</head>


<body>


<?php

if (file_exists(__DIR__ . '/admin_header.php')) {
    include 'admin_header.php';
}

?>


<main class="participants-page">


    <!-- =====================================================
         PAGE HEADER
    ===================================================== -->

    <section class="page-heading">

        <div>

            <span class="eyebrow">
                Volunteer Management
            </span>


            <h1>
                Event Participants
            </h1>


            <p class="event-details">

                <strong>
                    <?= htmlspecialchars($event['Name']) ?>
                </strong>

                <span>
                    📅 <?= htmlspecialchars($event['Date']) ?>
                </span>

                <span>
                    📍 <?= htmlspecialchars($event['Location']) ?>
                </span>

            </p>

        </div>


        <a
            href="view_volunteer_events.php"
            class="back-btn"
        >
            ← Volunteer Events
        </a>

    </section>



    <?php if (!empty($participants)): ?>


        <section class="participants-container">


            <div class="section-header">

                <div>

                    <h2>
                        Citizens Who Volunteered
                    </h2>

                    <p>
                        Participants registered for this volunteer event.
                    </p>

                </div>


                <span class="count-badge">

                    <?= count($participants) ?>

                    <?= count($participants) === 1
                        ? 'Participant'
                        : 'Participants'
                    ?>

                </span>

            </div>



            <div class="table-wrapper">

                <table class="participants-table">


                    <thead>

                        <tr>

                            <th>
                                Citizen
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Participation Proof
                            </th>

                            <th>
                                Certificate
                            </th>

                        </tr>

                    </thead>



                    <tbody>


                    <?php foreach ($participants as $participant): ?>


                        <tr>


                            <!-- CITIZEN -->

                            <td>

                                <div class="citizen-info">

                                    <div class="citizen-avatar">

                                        <?= htmlspecialchars(
                                            strtoupper(
                                                substr(
                                                    $participant['F_name'] ?? 'C',
                                                    0,
                                                    1
                                                )
                                            )
                                        ) ?>

                                    </div>


                                    <div class="citizen-name">

                                        <strong>

                                            <?= htmlspecialchars(
                                                ($participant['F_name'] ?? '') .
                                                ' ' .
                                                ($participant['L_name'] ?? '')
                                            ) ?>

                                        </strong>


                                        <span>
                                            Citizen #<?= (int)$participant['U_Id'] ?>
                                        </span>

                                    </div>

                                </div>

                            </td>



                            <!-- EMAIL -->

                            <td>

                                <?= htmlspecialchars(
                                    $participant['Email'] ?? ''
                                ) ?>

                            </td>



                            <!-- PROOF -->

                            <td>

                                <?php if (!empty($participant['Proof_Image'])): ?>

                                    <a
                                        href="view_proof.php?user_id=<?= (int)$participant['U_Id'] ?>&event_id=<?= $event_id ?>"
                                        class="proof-btn"
                                    >
                                        View Proof
                                    </a>

                                <?php else: ?>

                                    <span class="not-available">
                                        Not uploaded
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- CERTIFICATE STATUS -->

                            <td>

                                <?php if (!empty($participant['Certificate'])): ?>

                                    <span class="certificate-status certificate-issued">
                                        ✓ Issued
                                    </span>

                                <?php else: ?>

                                    <span class="certificate-status certificate-pending">
                                        Not issued
                                    </span>

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
                No Citizens Yet
            </h2>

            <p>
                No citizens have joined this volunteer event yet.
            </p>

        </section>


    <?php endif; ?>


</main>


<?php

if (file_exists(__DIR__ . '/admin_footer.php')) {
    include 'admin_footer.php';
}

?>

</body>

</html>