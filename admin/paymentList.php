<?php
session_start();
include "../config.php";

if (isset($_SESSION['id']) && isset($_SESSION['role'])) {


?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payments List | HACKFEST - 24</title>
        <?php include "./includes.php"; ?>
    </head>

    <body>
        <?php include "./error.php"; ?>


        <?php include "./header.php"; ?>

        <!-- Papers -->
        <div class="container">
            <div class="header">
                <h2>Payments List</h2>
            </div>

            <!-- Search Field -->
            <div class="search-form">
                <div class="input-group">
                    <input type="text" id="search-field" placeholder="Enter User ID">
                </div>
                <div class="input-group">
                    <select id="status-filter">
                        <option value="-1" selected>ALL</option>
                        <option value="NOT VERIFIED">NOT VERIFIED</option>
                        <option value="VERIFIED">VERIFIED</option>
                    </select>
                </div>
            </div>

            <div class="table">
                <table>
                    <tr>
                        <th>S.NO</th>
                        <th>Team ID</th>
                        <th>Payment Date</th>
                        <th>Transaction ID</th>
                        <th>STATUS</th>
                        <th>Verify</th>
                        <th>Action</th>
                    </tr>
                    <?php
                    $sql = "SELECT * FROM teams JOIN payment ON teams.T_ID = payment.T_ID";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        $i = 1;
                        while ($row = $result->fetch_assoc()) {
                    ?>
                            <tr id="row-<?php echo $row['T_ID']; ?>">
                                <td><?php echo $i; ?></td>
                                <td><?php echo $row['T_ID']; ?></td>

                                <td>
                                    <?php echo $row['PAYMENT_DATE']; ?>
                                </td>
                                <td>
                                    <?php echo $row['TRANSACTION_ID']; ?>
                                </td>
                                <td style="font-weight: 700; color: <?php echo ($row['STATUS'] == 'VERIFIED') ? "var(--success)" : "var(--danger)"; ?>;">
                                    <?php echo $row['STATUS']; ?>
                                </td>
                                <td>
                                    <button onclick="verifyPayment('<?php echo $row['T_ID']; ?>')">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </td>
                                <td>
                                    <button onclick="openFile('<?php echo $row['SCREENSHOT']; ?>')">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button onclick="downloadFile('<?php echo $row['SCREENSHOT']; ?>', '<?php echo $row['T_ID']; ?>')">
                                        <i class="fa-solid fa-download"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php
                            $i++;
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="7">No Payments Found</td>
                        </tr>
                    <?php
                    }
                    ?>
                </table>
            </div>
        </div>

        <div class="fileViewerContainer">
            <img src="" id="fileViewer" />
        </div>

        <script>
            $(".fileViewerContainer").click(function(event) {
                if (event.target == this) {
                    $(".fileViewerContainer").css('display', 'none');
                    $("#fileViewer").attr('src', '');
                }
            });

            function openFile(filePath) {
                var fileType = filePath.split('.').pop().toLowerCase();

                filePath = filePath.replace('./', '../');

                $(".fileViewerContainer").css('display', 'flex');
                $("#fileViewer").attr('src', filePath);
            }

            function downloadFile(filePath, t_id) {
                filePath = filePath.replace('./', '../');
                var a = document.createElement('a');
                a.href = filePath;
                a.download = 'Payment_Screenshot_' + t_id;
                a.click();
            }

            $("#search-field").on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $("table tr:not(:first-child)").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            $("#status-filter").on('change', function() {
                var value = $(this).val().toLowerCase();
                $("table tr:not(:first-child)").filter(function() {
                    if (value == -1) {
                        $(this).toggle(true);
                    } else {
                        $(this).toggle($(this).children(":nth-child(6)").text().toLowerCase().indexOf(value) > -1)
                    }
                });
            });

            function verifyPayment(teamID) {
                $.ajax({
                    url: './paymentOperation.php',
                    type: 'POST',
                    data: {
                        teamID: teamID,
                        action: 'verify'
                    },
                    success: function(data) {

                        data = JSON.parse(data);

                        if (data.status == 'success') {
                            $("#row-" + teamID).children(":nth-child(5)").css('color', 'var(--success)');
                            $("#row-" + teamID).children(":nth-child(5)").text('VERIFIED');
                        } else {
                            $("body").prepend(`<span class='message error'>${data.message}</span>`);
                        }
                    }
                });
            }
        </script>

    </body>

    </html>
<?php } else {
    header("Location: ./index.php");
}
