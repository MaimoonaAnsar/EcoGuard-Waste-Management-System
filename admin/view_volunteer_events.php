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
   GET VOLUNTEER EVENTS
   ========================================================= */


$stmt = $pdo->prepare("
    SELECT
        e.Event_Id,
        e.Name,
        e.Date,
        e.Location,
        COUNT(p.U_Id) AS Participant_Count
    FROM volunteer_event e
    LEFT JOIN user_participate_volunteer_event p
        ON p.Event_Id = e.Event_Id
    GROUP BY
        e.Event_Id,
        e.Name,
        e.Date,
        e.Location
    ORDER BY e.Date DESC
");


$stmt->execute();


$events = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>


<!DOCTYPE html>
<html lang="en">


<head>


    <meta charset="UTF-8">


    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>EcoGuard | Volunteer Events</title>


    <link rel="stylesheet" href="../css/ecoguard_responsive.css">


    <style>


        /* =====================================================
           MAIN
           ===================================================== */


        .admin-events-page {
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
            margin: 0 0 8px;

            color: #263b2b;

            font-size: clamp(28px, 4vw, 38px);
        }


        .page-heading p {
            margin: 0;

            color: #6d756e;

            font-size: 15px;
        }



        /* =====================================================
           BACK BUTTON
           ===================================================== */


        .dashboard-link {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 8px;

            height: 40px;

            padding: 0 16px;

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


        .dashboard-link:hover {
            background: #dfeade;

            color: #315c39;

            transform: translateX(-2px);
        }



        /* =====================================================
           TABLE CARD
           ===================================================== */


        .events-container {
            background: #fff;

            border: 1px solid #e1e7e0;

            border-radius: 12px;

            overflow: hidden;

            box-shadow: 0 4px 18px rgba(35,58,40,.05);
        }



        .table-header {
            padding: 22px 24px;

            border-bottom: 1px solid #e5e9e4;
        }


        .table-header h2 {
            margin: 0 0 5px;

            color: #293c2d;

            font-size: 19px;
        }


        .table-header p {
            margin: 0;

            color: #7a827b;

            font-size: 13px;
        }



        /* =====================================================
           TABLE
           ===================================================== */


        .table-wrapper {
            width: 100%;

            overflow-x: auto;

            -webkit-overflow-scrolling: touch;
        }


        .events-table {
            width: 100%;

            min-width: 850px;

            border-collapse: collapse;

            text-align: left;
        }


        .events-table thead {
            background: #f7f9f6;
        }


        .events-table th {
            padding: 14px 20px;

            border-bottom: 1px solid #e1e7e0;

            color: #687168;

            font-size: 12px;
            font-weight: 700;

            text-transform: uppercase;

            white-space: nowrap;
        }


        .events-table td {
            padding: 18px 20px;

            border-bottom: 1px solid #edf0ec;

            color: #414a43;

            font-size: 14px;

            vertical-align: middle;
        }


        .events-table tbody tr:last-child td {
            border-bottom: none;
        }


        .events-table tbody tr:hover {
            background: #fafcf9;
        }



        /* =====================================================
           EVENT
           ===================================================== */


        .event-name {
            display: flex;

            align-items: center;

            gap: 12px;

            min-width: 220px;
        }


        .event-icon {
            width: 40px;
            height: 40px;

            flex: 0 0 40px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 9px;

            background: #edf5ed;

            font-size: 19px;
        }


        .event-name strong {
            display: block;

            margin-bottom: 4px;

            color: #2d3d30;

            font-size: 14px;
        }


        .event-name span {
            color: #8a918b;

            font-size: 12px;
        }



        /* =====================================================
           INFO
           ===================================================== */


        .table-info {
            display: flex;

            align-items: center;

            gap: 8px;

            white-space: nowrap;
        }



        /* =====================================================
           VOLUNTEER COUNT
           ===================================================== */


        .volunteer-badge {
            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding: 7px 10px;

            border-radius: 7px;

            background: #f1f6f1;

            color: #3f6547;

            font-size: 12px;

            font-weight: 700;

            white-space: nowrap;
        }



        /* =====================================================
           ACTION BUTTONS
           ===================================================== */


        .action-buttons {
            display: flex;

            align-items: center;

            gap: 8px;

            flex-wrap: wrap;
        }



        /* =====================================================
           VIEW BUTTON
           ===================================================== */


        .view-btn {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 7px;

            padding: 9px 14px;

            border-radius: 7px;

            background: #3f7048;

            color: white;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;

            white-space: nowrap;

            transition: .2s ease;
        }


        .view-btn:hover {
            background: #315c39;

            transform: translateY(-1px);
        }



        /* =====================================================
           DELETE BUTTON
           ===================================================== */


        .delete-btn {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 7px;

            padding: 9px 14px;

            border-radius: 7px;

            background: #fff1f1;

            border: 1px solid #f0caca;

            color: #b42323;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;

            white-space: nowrap;

            transition: .2s ease;
        }


        .delete-btn:hover {
            background: #dc2626;

            border-color: #dc2626;

            color: #ffffff;

            transform: translateY(-1px);
        }



        /* =====================================================
           EMPTY
           ===================================================== */


        .empty-state {
            display: flex;

            flex-direction: column;

            align-items: center;
            justify-content: center;

            min-height: 350px;

            padding: 40px;

            background: white;

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
        }


        .empty-state p {
            margin: 0 0 20px;

            color: #747c75;

            font-size: 14px;
        }



        /* =====================================================
           RESPONSIVE
           ===================================================== */


        @media (max-width: 768px) {


            .admin-events-page {
                width: calc(100% - 24px);

                padding: 25px 0 40px;
            }


            .page-heading {
                flex-direction: column;

                align-items: flex-start;

                gap: 18px;
            }


            .dashboard-link {
                width: auto;
            }


            .events-table {
                min-width: 850px;
            }


            .events-table th,
            .events-table td {
                padding: 14px 16px;
            }


            .action-buttons {
                flex-direction: column;

                align-items: stretch;
            }


            .view-btn,
            .delete-btn {
                width: 100%;

                justify-content: center;
            }


        }



        @media (max-width: 480px) {


            .admin-events-page {
                width: calc(100% - 20px);
            }


            .page-heading h1 {
                font-size: 25px;
            }


        }


    </style>


</head>



<body>


<?php include 'admin_header.php'; ?>



<main class="admin-events-page">


    <!-- PAGE HEADER -->


    <section class="page-heading">


        <div>


            <span class="eyebrow">
                Admin
            </span>


            <h1>
                Volunteer Events
            </h1>


            <p>
                View volunteer events and the citizens who participated.
            </p>


        </div>



        <a
            href="admin_dash.php"
            class="dashboard-link"
        >
            <span>←</span>
            Dashboard
        </a>


    </section>



    <!-- EVENTS -->


    <?php if (!empty($events)): ?>


        <section class="events-container">


            <div class="table-header">


                <h2>
                    Volunteer Events
                </h2>


                <p>
                    <?= count($events) ?>
                    event<?= count($events) !== 1 ? 's' : '' ?>
                    available
                </p>


            </div>



            <div class="table-wrapper">


                <table class="events-table">


                    <thead>


                        <tr>


                            <th>Event</th>


                            <th>Date</th>


                            <th>Location</th>


                            <th>Volunteers</th>


                            <th>Action</th>


                        </tr>


                    </thead>



                    <tbody>


                    <?php foreach ($events as $event): ?>


                        <tr>


                            <td>


                                <div class="event-name">


                                    <div class="event-icon">
                                        🌱
                                    </div>


                                    <div>


                                        <strong>
                                            <?= htmlspecialchars($event['Name']) ?>
                                        </strong>


                                        <span>
                                            Event #<?= (int)$event['Event_Id'] ?>
                                        </span>


                                    </div>


                                </div>


                            </td>



                            <td>


                                <div class="table-info">
                                    📅
                                    <?= htmlspecialchars($event['Date']) ?>
                                </div>


                            </td>



                            <td>


                                <div class="table-info">
                                    📍
                                    <?= htmlspecialchars($event['Location']) ?>
                                </div>


                            </td>



                            <td>


                                <span class="volunteer-badge">


                                    👥


                                    <?= (int)$event['Participant_Count'] ?>


                                    <?= (int)$event['Participant_Count'] === 1
                                        ? 'Volunteer'
                                        : 'Volunteers'
                                    ?>


                                </span>


                            </td>



                            <!-- =================================================
                                 VIEW + DELETE
                            ================================================== -->

                            <td>


                                <div class="action-buttons">


                                    <!-- VIEW CITIZENS -->

                                    <a
                                        href="event_participants.php?id=<?= (int)$event['Event_Id'] ?>"
                                        class="view-btn"
                                    >
                                        👥 View Citizens →
                                    </a>


                                    <!-- DELETE EVENT -->

                                    <a
                                        href="delete_event.php?id=<?= (int)$event['Event_Id'] ?>"
                                        class="delete-btn"
                                        onclick="return confirm('Are you sure you want to delete this volunteer event? This will also remove the participation records for this event. This action cannot be undone.');"
                                    >
                                        🗑 Delete
                                    </a>


                                </div>


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
                🌱
            </div>


            <h2>
                No Volunteer Events
            </h2>


            <p>
                Volunteer events posted by the Admin will appear here.
            </p>


            <a
                href="admin_dash.php"
                class="dashboard-link"
            >
                ← Dashboard
            </a>


        </section>


    <?php endif; ?>


</main>



<?php include 'admin_footer.php'; ?>


</body>
</html>