<!-- views/site/chart.php -->
<?php
$this->title = 'Bar Chart';
$this->params['breadcrumbs'][] = $this->title;
?>

<canvas id="myBarChart" width="400" height="200"></canvas>

<?php
$labels = ['January', 'February', 'March'];
$data = [10, 20, 30];
$this->registerJs("
const ctx = document.getElementById('myBarChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: " . json_encode($labels) . ",
        datasets: [{
            label: 'Sales',
            data: " . json_encode($data) . ",
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor:  'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true }
        }
    }
});
");
?>
