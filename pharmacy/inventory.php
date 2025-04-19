<?php 
require_once 'connect.php'; 
?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-lg" style="width: 100%; min-height: 80vh;">
                    <div class="card-header text-center bg-primary text-white">
                        <h3><b>Inventory</b></h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Product Name</th>
                                        <th class="text-center">Base Unit</th>
                                        <th class="text-center">Smallest Unit</th>
                                        <th class="text-center">Stock In (Base)</th>
                                        <th class="text-center">Stock Out (Base)</th>
                                        <th class="text-center">Expired (Base)</th>
                                        <th class="text-center">Stock Available (Base)</th>
                                        <th class="text-center">Stock Available (Smallest)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    $product = $conn->query("SELECT * FROM drug_product ORDER BY name ASC");

                                    while ($row = $product->fetch_assoc()):
                                        // Retrieve unit type and conversion details
                                        $base_unit = $row['base_unit'];  
                                        $smallest_unit = $row['smallest_unit'];  
                                        $conversion_factor = $row['conversion_factor'];  

                                        // Retrieve total stock-in (purchased stock)
                                        $inn_query = $conn->query("SELECT SUM(quantity) AS inn FROM inventory WHERE type = 1 AND drug_id = " . $row['id']);
                                        $inn = $inn_query && $inn_query->num_rows > 0 ? $inn_query->fetch_array()['inn'] : 0;

                                        // Retrieve total quantity sold (sum of all sales)
                                        $out_query = $conn->query("SELECT SUM(quantity) AS `out` FROM sales WHERE product_id = " . $row['id']);
                                        $out = $out_query && $out_query->num_rows > 0 ? $out_query->fetch_array()['out'] : 0;

                                        // Retrieve total expired stock
                                        $ex_query = $conn->query("SELECT SUM(quantity) AS ex FROM expired_product WHERE product_id = " . $row['id']);
                                        $ex = $ex_query && $ex_query->num_rows > 0 ? $ex_query->fetch_array()['ex'] : 0;

                                        // Calculate available stock in base unit
                                        $available_base = $inn - $out - $ex;

                                        // Convert available stock to smallest unit
                                        $available_smallest = ($conversion_factor > 0) ? $available_base * $conversion_factor : $available_base;
                                    ?>

                                    <tr>
                                        <td class="text-center"><?php echo $i++; ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($base_unit); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($smallest_unit); ?></td>
                                        <td class="text-center"><?php echo $inn > 0 ? $inn : 0; ?></td>
                                        <td class="text-center"><?php echo $out > 0 ? $out : 0; ?></td>
                                        <td class="text-center"><?php echo $ex > 0 ? $ex : 0; ?></td>
                                        <td class="text-center"><b><?php echo $available_base; ?></b></td>
                                        <td class="text-center"><b><?php echo $available_smallest; ?></b></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div> <!-- End of table-responsive -->
                    </div> <!-- End of card-body -->
                </div> <!-- End of card -->
            </div>
        </div>
    </div>
</div>
