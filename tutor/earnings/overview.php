<?php
// Earnings Overview page for Tutors
$page_title = 'Earnings Overview';
include '../../header.php';
?>

<div class="container">
    <h2>Earnings Overview</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>500 π</h5>
                    <p>Total Pi Coin Earnings</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>100 π</h5>
                    <p>This Month</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>15</h5>
                    <p>Completed Tasks</p>
                </div>
            </div>
        </div>
    </div>
    <div class="widget mt-4">
        <h3>Recent Transactions</h3>
        <ul>
            <li>Task completion: 50 π - 2023-10-01</li>
            <li>Publication bonus: 25 π - 2023-09-28</li>
            <li>Task completion: 75 π - 2023-09-25</li>
        </ul>
    </div>
</div>

<?php include '../../footer.php'; ?>