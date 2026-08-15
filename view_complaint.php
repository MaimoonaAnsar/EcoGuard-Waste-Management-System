<?php
session_start();

include __DIR__ . '/includes/db.php';
include __DIR__ . '/includes/auth.php';
include __DIR__ . '/includes/csrf.php';

requireRole(2, 'login.php');

$admin_id = $_SESSION['user_id'];

/* =========================================================
   GET COMPLAINT ID
========================================================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Complaint ID.");
}

$complaint_id = (int)$_GET['id'];

/* =========================================================
   FETCH COMPLAINT
========================================================= */

$stmt = $pdo->prepare("
    SELECT 
        c.*,
        u.F_name,
        u.L_name,
        u.Email,
        u.Tele,
        a.F_name AS A_F,
        a.L_name AS A_L
    FROM complaint c
    JOIN users u 
        ON c.U_Id = u.U_Id
    LEFT JOIN users a 
        ON c.Assigned_To = a.U_Id
    WHERE c.C_Id = ?
");

$stmt->execute([$complaint_id]);

$complaint = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$complaint) {
    die("Complaint not found.");
}

/* =========================================================
   UPDATE STATUS
========================================================= */

$message = '';

if (isset($_POST['update_status'])) {

    csrf_verify();

    $new_status = $_POST['status'] ?? '';
    $assigned_user_input = trim($_POST['assigned_user'] ?? '');

    if (empty($new_status)) {

        $message = "Please select a status.";

    } elseif (
        in_array(
            $new_status,
            [
                'Grama Niladhari',
                'Municipal Council',
                'Urban Council',
                'Pradeshiya Sabha'
            ],
            true
        )
        && empty($assigned_user_input)
    ) {

        $message = "Please specify the user for escalation.";

    } else {

        /* =============================================
           APPROVE COMPLAINT
        ============================================= */

        if ($new_status === 'Approved') {

            $stmtUpdate = $pdo->prepare("
                UPDATE complaint
                SET Status = 'Approved',
                    Assigned_To = NULL
                WHERE C_Id = ?
            ");

            if ($stmtUpdate->execute([$complaint_id])) {

                $message = "Complaint approved successfully!";

                $complaint['Status'] = 'Approved';
                $complaint['Assigned_To'] = null;

            } else {

                $message = "Failed to approve complaint.";
            }

        } else {

            /* =========================================
               ROLE MAPPING
            ========================================= */

            $role_id_map = [

                'Grama Niladhari' => 5,

                'Municipal Council' => 4,

                'Urban Council' => 4,

                'Pradeshiya Sabha' => 3

            ];

            $role_id = $role_id_map[$new_status];

            /* =========================================
               FIND ASSIGNED USER
            ========================================= */

            $stmtUser = $pdo->prepare("
                SELECT U_Id
                FROM users
                WHERE (
                    F_name LIKE ?
                    OR L_name LIKE ?
                    OR Email LIKE ?
                )
                AND Role_Id = ?
            ");

            $search = '%' . $assigned_user_input . '%';

            $stmtUser->execute([
                $search,
                $search,
                $search,
                $role_id
            ]);

            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($user) {

                $assigned_id = (int)$user['U_Id'];

                /* =====================================
                   UPDATE COMPLAINT
                ===================================== */

                $stmtUpdate = $pdo->prepare("
                    UPDATE complaint
                    SET Status = ?,
                        Assigned_To = ?
                    WHERE C_Id = ?
                ");

                if (
                    $stmtUpdate->execute([
                        $new_status,
                        $assigned_id,
                        $complaint_id
                    ])
                ) {

                    $message =
                        "Complaint escalated to " .
                        $new_status .
                        " successfully!";

                    $complaint['Status'] = $new_status;
                    $complaint['Assigned_To'] = $assigned_id;

                    /* Refresh assigned person's name */

                    $stmtAssigned = $pdo->prepare("
                        SELECT F_name, L_name
                        FROM users
                        WHERE U_Id = ?
                    ");

                    $stmtAssigned->execute([$assigned_id]);

                    $assignedUser =
                        $stmtAssigned->fetch(PDO::FETCH_ASSOC);

                    if ($assignedUser) {

                        $complaint['A_F'] =
                            $assignedUser['F_name'];

                        $complaint['A_L'] =
                            $assignedUser['L_name'];
                    }

                } else {

                    $message =
                        "Failed to escalate complaint.";
                }

            } else {

                $message =
                    "No matching user found for " .
                    $new_status . ".";
            }
        }
    }
}

/* =========================================================
   ASSIGNED USER NAME
========================================================= */

$assigned_name = '';

if (!empty($complaint['Assigned_To'])) {

    $assigned_name = trim(
        ($complaint['A_F'] ?? '') .
        ' ' .
        ($complaint['A_L'] ?? '')
    );
}

/* =========================================================
   CHECK LOCATION
========================================================= */

$hasLatLng =
    !empty($complaint['Latitude']) &&
    !empty($complaint['Longitude']);

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        View Complaint | EcoGuard Admin
    </title>

    <link
        rel="stylesheet"
        href="css/theme.css"
    >

    <?php if ($hasLatLng): ?>

        <link
            rel="stylesheet"
            href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        >

    <?php endif; ?>


    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f8f4;
        }

        /* =============================================
           HEADER
        ============================================= */

        .eg-page-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            padding: 18px 30px;

            background: #ffffff;

            border-bottom: 1px solid #dfe7dd;
        }

        .eg-page-header h1 {

            margin: 0;

            color: #263b2b;

            font-size: 26px;
        }

        .dashboard-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            padding: 10px 16px;

            border: 1px solid #d5e2d3;

            border-radius: 8px;

            background: #eef5ec;

            color: #3f7048;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;

            transition: .2s ease;
        }

        .dashboard-btn:hover {

            background: #dfeade;

            color: #315c39;

            transform: translateY(-1px);
        }

        /* =============================================
           MAIN
        ============================================= */

        .dashboard {

            width: min(1250px, calc(100% - 40px));

            margin: 25px auto 40px;
        }

        .main-content {
            width: 100%;
        }

        /* =============================================
           SUCCESS MESSAGE
        ============================================= */

        .success {

            margin: 0 0 18px;

            padding: 13px 16px;

            background: #eaf7ed;

            border: 1px solid #cce8d2;

            border-radius: 8px;

            color: #28713b;

            font-weight: 600;
        }

        /* =============================================
           TOP SECTION
        ============================================= */

        .complaint-top-section {

            display: grid;

            grid-template-columns:
                minmax(0, 1.25fr)
                minmax(350px, .75fr);

            gap: 22px;

            align-items: start;
        }

        /* =============================================
           PANELS
        ============================================= */

        .details-panel,
        .map-panel {

            background: #ffffff;

            border: 1px solid #dfe7dd;

            border-radius: 12px;

            overflow: hidden;

            box-shadow:
                0 4px 18px rgba(35, 58, 40, .05);
        }

        .panel-title {

            margin: 0;

            padding: 15px 18px;

            background: #f7f9f6;

            border-bottom: 1px solid #e5e9e4;

            color: #293c2d;

            font-size: 18px;
        }

        /* =============================================
           COMPLAINT TABLE
        ============================================= */

        .complaint-details {

            width: 100%;

            border-collapse: collapse;
        }

        .complaint-details th {

            width: 145px;

            padding: 11px 15px;

            background: #fafcf9;

            border-bottom: 1px solid #edf0ec;

            color: #536057;

            font-size: 13px;

            font-weight: 700;

            text-align: left;

            vertical-align: top;
        }

        .complaint-details td {

            padding: 11px 15px;

            border-bottom: 1px solid #edf0ec;

            color: #414a43;

            font-size: 13px;

            line-height: 1.5;

            vertical-align: top;
        }

        .complaint-details tr:last-child th,
        .complaint-details tr:last-child td {

            border-bottom: none;
        }

        .complaint-details img {

            display: block;

            max-width: 100%;

            width: 260px;

            max-height: 180px;

            object-fit: cover;

            border-radius: 10px;

            border: 1px solid #dfe7dd;
        }

        /* =============================================
           MAP
        ============================================= */

        .map-heading {

            padding: 15px 18px;

            background: #f7f9f6;

            border-bottom: 1px solid #e5e9e4;

            color: #293c2d;

            font-size: 18px;

            font-weight: 700;
        }

        .eg-map {

            width: calc(100% - 30px);

            height: 390px;

            margin: 15px;

            border: 1px solid #dfe7dd;

            border-radius: 10px;

            overflow: hidden;
        }

        .no-map {

            min-height: 390px;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px;

            color: #7a847b;

            text-align: center;

            font-size: 14px;
        }

        /* =============================================
           ESCALATION
        ============================================= */

        .escalation-panel {

            margin-top: 25px;

            padding: 25px;

            background: #ffffff;

            border: 1px solid #dfe7dd;

            border-radius: 12px;

            box-shadow:
                0 4px 18px rgba(35, 58, 40, .05);
        }

        .escalation-panel h3 {

            margin: 0 0 18px;

            color: #293c2d;

            font-size: 20px;
        }

        .escalation-panel label {

            display: block;

            margin-bottom: 8px;

            color: #414a43;

            font-size: 14px;

            font-weight: 700;
        }

        .escalation-panel select,
        .escalation-panel input {

            width: 100%;

            min-height: 44px;

            padding: 10px 12px;

            border: 1px solid #d5dfd3;

            border-radius: 8px;

            background: #ffffff;

            color: #344239;

            font-size: 14px;

            outline: none;
        }

        .escalation-panel select:focus,
        .escalation-panel input:focus {

            border-color: #6c9873;

            box-shadow:
                0 0 0 3px rgba(108, 152, 115, .12);
        }

        .update-btn {

            margin-top: 15px;

            min-height: 44px;

            padding: 0 20px;

            border: none;

            border-radius: 8px;

            background: #3f7048;

            color: #ffffff;

            font-size: 14px;

            font-weight: 700;

            cursor: pointer;

            transition: .2s ease;
        }

        .update-btn:hover {

            background: #315c39;

            transform: translateY(-1px);
        }

        #assigned-user-div {

            margin-top: 14px;
        }

        /* =============================================
           RESPONSIVE
        ============================================= */

        @media (max-width: 900px) {

            .complaint-top-section {

                grid-template-columns: 1fr;
            }

            .eg-map {

                height: 350px;
            }
        }

        @media (max-width: 600px) {

            .eg-page-header {

                padding: 15px;

                flex-direction: column;

                align-items: stretch;
            }

            .eg-page-header h1 {

                font-size: 23px;
            }

            .dashboard-btn {

                width: 100%;
            }

            .dashboard {

                width: calc(100% - 20px);

                margin-top: 15px;
            }

            .complaint-details th {

                width: 105px;

                padding: 9px;

                font-size: 12px;
            }

            .complaint-details td {

                padding: 9px;

                font-size: 12px;
            }

            .escalation-panel {

                padding: 18px;
            }

            .eg-map {

                width: calc(100% - 20px);

                height: 300px;

                margin: 10px;
            }
        }

    </style>

</head>


<body>


<!-- =========================================================
     HEADER
========================================================= -->

<div class="eg-page-header">

    <h1>
        View Complaint
    </h1>

    <a
        href="admin/admin_dash.php"
        class="dashboard-btn"
    >
        ← Back to Dashboard
    </a>

</div>


<!-- =========================================================
     MAIN
========================================================= -->

<div class="dashboard">

    <div class="main-content">


        <?php if (!empty($message)): ?>

            <p class="success">
                <?= htmlspecialchars($message) ?>
            </p>

        <?php endif; ?>


        <!-- =================================================
             COMPLAINT DETAILS + MAP
        ================================================== -->

        <div class="complaint-top-section">


            <!-- =============================================
                 LEFT SIDE
            ============================================== -->

            <section class="details-panel">

                <h2 class="panel-title">
                    Complaint Details
                </h2>


                <table class="complaint-details">

                    <tr>

                        <th>ID:</th>

                        <td>
                            <?= htmlspecialchars($complaint['C_Id']) ?>
                        </td>

                    </tr>


                    <tr>

                        <th>Submitted By:</th>

                        <td>
                            <?= htmlspecialchars(
                                $complaint['F_name'] . ' ' .
                                $complaint['L_name']
                            ) ?>
                        </td>

                    </tr>


                    <tr>

                        <th>Email:</th>

                        <td>
                            <?= htmlspecialchars($complaint['Email']) ?>
                        </td>

                    </tr>


                    <tr>

                        <th>Telephone:</th>

                        <td>
                            <?= htmlspecialchars($complaint['Tele']) ?>
                        </td>

                    </tr>


                    <tr>

                        <th>Location:</th>

                        <td>
                            <?= htmlspecialchars($complaint['Location']) ?>
                        </td>

                    </tr>


                    <tr>

                        <th>Severity:</th>

                        <td>
                            <?= htmlspecialchars($complaint['Severity']) ?>
                        </td>

                    </tr>


                    <tr>

                        <th>Status:</th>

                        <td>

                            <strong>
                                <?= htmlspecialchars($complaint['Status']) ?>
                            </strong>

                            <?php if (!empty($assigned_name)): ?>

                                &nbsp;(
                                <em>
                                    Assigned to:
                                    <?= htmlspecialchars($assigned_name) ?>
                                </em>
                                )

                            <?php endif; ?>

                        </td>

                    </tr>


                    <tr>

                        <th>Details:</th>

                        <td>
                            <?= nl2br(
                                htmlspecialchars(
                                    $complaint['Details']
                                )
                            ) ?>
                        </td>

                    </tr>


                    <?php if (!empty($complaint['Images'])): ?>

                        <tr>

                            <th>Image:</th>

                            <td>

                                <img
                                    src="<?= htmlspecialchars(
                                        $complaint['Images']
                                    ) ?>"
                                    alt="Complaint Image"
                                >

                            </td>

                        </tr>

                    <?php endif; ?>

                </table>

            </section>


            <!-- =============================================
                 RIGHT SIDE - LEAFLET
            ============================================== -->

            <section class="map-panel">

                <div class="map-heading">
                    📍 Complaint Location
                </div>


                <?php if ($hasLatLng): ?>

                    <div
                        id="map"
                        class="eg-map"
                    ></div>

                <?php else: ?>

                    <div class="no-map">

                        No location coordinates were
                        provided for this complaint.

                    </div>

                <?php endif; ?>

            </section>


        </div>


        <!-- =================================================
             ESCALATION
        ================================================== -->

        <section class="escalation-panel">

            <h3>
                Update Status / Assign Authority
            </h3>


            <form method="POST">

                <?php csrf_field(); ?>


                <label for="status">
                    Status:
                </label>


                <select
                    name="status"
                    id="status"
                    required
                    onchange="toggleAssignedUser(this.value)"
                >

                    <option value="">
                        -- Select Status --
                    </option>


                    <option
                        value="Approved"
                        <?= ($complaint['Status'] === 'Approved')
                            ? 'selected'
                            : '' ?>
                    >
                        Approved
                    </option>


                    <option
                        value="Grama Niladhari"
                        <?= ($complaint['Status'] === 'Grama Niladhari')
                            ? 'selected'
                            : '' ?>
                    >
                        Escalate to Grama Niladhari
                    </option>


                    <option
                        value="Municipal Council"
                        <?= ($complaint['Status'] === 'Municipal Council')
                            ? 'selected'
                            : '' ?>
                    >
                        Escalate to Municipal Council
                    </option>


                    <option
                        value="Urban Council"
                        <?= ($complaint['Status'] === 'Urban Council')
                            ? 'selected'
                            : '' ?>
                    >
                        Escalate to Urban Council
                    </option>


                    <option
                        value="Pradeshiya Sabha"
                        <?= ($complaint['Status'] === 'Pradeshiya Sabha')
                            ? 'selected'
                            : '' ?>
                    >
                        Escalate to Pradeshiya Sabha
                    </option>

                </select>


                <div
                    id="assigned-user-div"
                    style="display: none;"
                >

                    <label for="assigned_user">
                        Enter User Name or Email:
                    </label>


                    <input
                        type="text"
                        id="assigned_user"
                        name="assigned_user"
                        placeholder="e.g., John Doe or john@example.com"
                    >

                </div>


                <button
                    type="submit"
                    name="update_status"
                    class="update-btn"
                >
                    Update Status
                </button>

            </form>

        </section>


    </div>

</div>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>

function toggleAssignedUser(status) {

    const div =
        document.getElementById('assigned-user-div');

    const escalationStatuses = [
        'Grama Niladhari',
        'Municipal Council',
        'Urban Council',
        'Pradeshiya Sabha'
    ];

    if (escalationStatuses.includes(status)) {

        div.style.display = 'block';

    } else {

        div.style.display = 'none';

    }
}


document.addEventListener('DOMContentLoaded', function () {

    const status =
        <?= json_encode($complaint['Status']) ?>;

    toggleAssignedUser(status);

});

</script>


<?php if ($hasLatLng): ?>

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    ></script>


    <script>

        const position = [
            <?= (float)$complaint['Latitude'] ?>,
            <?= (float)$complaint['Longitude'] ?>
        ];


        const map = L.map('map').setView(
            position,
            16
        );


        L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright">' +
                    'OpenStreetMap</a> contributors',

                maxZoom: 19
            }
        ).addTo(map);


        L.marker(position)
            .addTo(map)
            .bindPopup('Complaint Location')
            .openPopup();

    </script>

<?php endif; ?>


</body>

</html>