// JavaScript to update time dynamically
function updateTime() {
    const now = new Date();
    document.getElementById("live-time").textContent = now.toLocaleTimeString();
}
setInterval(updateTime, 1000);
updateTime();
// Sample data for sales log
const salesData = [
    {orderId: "001", productName: "Product A", quantity: 2, unitPrice: 50.00, totalPrice: 100.00, salesDate: "2025-01-25", salesperson: "John Doe"},
    {orderId: "002", productName: "Product B", quantity: 1, unitPrice: 25.00, totalPrice: 25.00, salesDate: "2025-01-25", salesperson: "Jane Smith"},
    {orderId: "003", productName: "Product C", quantity: 3, unitPrice: 30.00, totalPrice: 90.00, salesDate: "2025-01-24", salesperson: "John Doe"},
    // Add more sample data for testing...
];

let currentPage = 1;
const rowsPerPage = 10;

// Function to display sales data
function displaySalesData() {
    const tableBody = document.querySelector("#salesTable tbody");
    tableBody.innerHTML = "";
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const paginatedData = salesData.slice(startIndex, endIndex);

    paginatedData.forEach((sale) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${sale.orderId}</td>
            <td>${sale.productName}</td>
            <td>${sale.quantity}</td>
            <td>$${sale.unitPrice.toFixed(2)}</td>
            <td>$${sale.totalPrice.toFixed(2)}</td>
            <td>${sale.salesDate}</td>
        `;
        tableBody.appendChild(row);
    });

    document.getElementById("prevPage").disabled = currentPage === 1;
    document.getElementById("nextPage").disabled = currentPage * rowsPerPage >= salesData.length;
}

// Pagination functions
function prevPage() {
    if (currentPage > 1) {
        currentPage--;
        displaySalesData();
    }
}

function nextPage() {
    if (currentPage * rowsPerPage < salesData.length) {
        currentPage++;
        displaySalesData();
    }
}

// Search functionality
document.getElementById("searchQuery").addEventListener("input", function () {
    const query = this.value.toLowerCase();
    const filteredData = salesData.filter(sale => 
        sale.orderId.toLowerCase().includes(query) || 
        sale.productName.toLowerCase().includes(query) || 
        sale.salesperson.toLowerCase().includes(query)
    );
    displayFilteredData(filteredData);
});

function displayFilteredData(filteredData) {
    const tableBody = document.querySelector("#salesTable tbody");
    tableBody.innerHTML = "";
    filteredData.forEach((sale) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${sale.orderId}</td>
            <td>${sale.productName}</td>
            <td>${sale.quantity}</td>
            <td>$${sale.unitPrice.toFixed(2)}</td>
            <td>$${sale.totalPrice.toFixed(2)}</td>
            <td>${sale.salesDate}</td>
        `;
        tableBody.appendChild(row);
    });
}

// Initialize the display
displaySalesData();