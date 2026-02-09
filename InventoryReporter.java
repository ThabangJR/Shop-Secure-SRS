import java.sql.*;
import java.text.DecimalFormat;
import java.util.logging.Logger;
import java.util.logging.Level;

public class InventoryReporter {

    // FIX: Added 'allowPublicKeyRetrieval=true' to resolve connection errors
    // related to MySQL's caching_sha2_password authentication plugin.
    private static final String URL = "jdbc:mysql://localhost:3306/ShopSecureDB?allowPublicKeyRetrieval=true&useSSL=false";
    private static final String USER = "root";
    private static final String PASS = "Thabang@23768"; 

    private static final Logger LOGGER = Logger.getLogger(InventoryReporter.class.getName());

    public static void main(String[] args) {
        // Formatter for currency output (e.g., 5,000.00)
        DecimalFormat df = new DecimalFormat("#,##0.00");
        
        StringBuilder report = new StringBuilder("\n====================================================================\n");
        report.append("  HIGH-VALUE STOCK REPORT (Generated: ").append(new java.util.Date()).append(")\n");
        report.append("====================================================================\n\n");

        try {
            Class.forName("com.mysql.cj.jdbc.Driver");

            try (Connection conn = DriverManager.getConnection(URL, USER, PASS);
                 PreparedStatement pstmt = conn.prepareStatement(
                     "SELECT name, stockQty, price, (stockQty * price) AS total_value FROM Products WHERE stockQty * price > 5000 ORDER BY total_value DESC"
                 );
                 ResultSet rs = pstmt.executeQuery()) 
            {
                
                // ADJUSTED WIDTHS: Name (35), Quantity (15), Total Value (15)
                report.append(String.format("%-35s %-15s %-15s\n", "Product Name", "Quantity", "Total Value (ZAR)"));
                report.append("---------------------------------------------------------------------\n");
                
                double overallValue = 0;
                boolean foundResults = false;

                while (rs.next()) {
                    foundResults = true;
                    String name = rs.getString("name");
                    int stock = rs.getInt("stockQty");
                    double value = rs.getDouble("total_value");

                    overallValue += value;
                    
                    // Use the same format for the data lines
                    report.append(String.format("%-35s %-15d R%-15s\n", name, stock, df.format(value)));
                }

                if (!foundResults) {
                     report.append("No products found where total value exceeds R5,000.\n");
                }
                
                report.append("---------------------------------------------------------------------\n");
                report.append(String.format("%-50s R%-15s\n", "TOTAL (OF HIGH-VALUE STOCK):", df.format(overallValue)));
                report.append("====================================================================\n");
                
                System.out.print(report.toString()); 
                
            }
        } catch (ClassNotFoundException e) {
            System.err.println("CRITICAL ERROR: MySQL JDBC Driver not found. Ensure the connector JAR is in the classpath.");
            LOGGER.log(Level.SEVERE, "JDBC Driver Error", e);
        } catch (SQLException e) {
            System.err.println("DB Connection/Query Error in Java: Could not generate report.");
            System.err.println("SQL State: " + e.getSQLState() + " | Error Code: " + e.getErrorCode());
            LOGGER.log(Level.SEVERE, "SQL Error", e);
        }
    }
}
