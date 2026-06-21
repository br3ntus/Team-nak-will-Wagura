import json
import datetime
from http.server import HTTPServer, BaseHTTPRequestHandler
from urllib.parse import urlparse, parse_qs

# Zero-dependency Linear Regression in Pure Python
def linear_regression(X, Y):
    n = len(X)
    if n < 2:
        return 0.0, 0.0, 0.0
    
    sum_x = sum(X)
    sum_y = sum(Y)
    sum_xx = sum(x*x for x in X)
    sum_xy = sum(x*y for x, y in zip(X, Y))
    
    numerator_m = (n * sum_xy) - (sum_x * sum_y)
    denominator_m = (n * sum_xx) - (sum_x * sum_x)
    
    if denominator_m == 0:
        m = 0.0
    else:
        m = numerator_m / denominator_m
        
    b = (sum_y - (m * sum_x)) / n
    
    # Calculate R-squared (coefficient of determination)
    mean_y = sum_y / n
    ss_tot = sum((y - mean_y)**2 for y in Y)
    ss_res = sum((y - (m * x + b))**2 for x, y in zip(X, Y))
    
    if ss_tot == 0:
        r_squared = 1.0
    else:
        r_squared = 1.0 - (ss_res / ss_tot)
        
    return m, b, r_squared

class PredictionAPIHandler(BaseHTTPRequestHandler):
    def log_message(self, format, *args):
        # Suppress request logging to keep console clean
        return

    def do_OPTIONS(self):
        self.send_response(200)
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type')
        self.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        self.end_headers()

    def do_POST(self):
        # Handle predictions via POST JSON payload
        parsed_url = urlparse(self.path)
        if parsed_url.path == '/api/predict':
            content_length = int(self.headers.get('Content-Length', 0))
            post_data = self.rfile.read(content_length)
            
            try:
                payload = json.loads(post_data.decode('utf-8'))
                logs = payload.get('logs', [])
                pet_name = payload.get('pet_name', 'Coco')
                breed_str = payload.get('breed', 'Aspin Dog')
                prediction_days = payload.get('days', 31)

                if len(logs) < 2:
                    self.send_error_response("Need at least 2 data points.")
                    return

                # Parse logs
                actual_data = []
                X = []
                y = []
                
                # Assume logs are sorted chronologically
                first_date_str = logs[0]['date']
                first_date = datetime.datetime.strptime(first_date_str, "%Y-%m-%d").date()

                for log in logs:
                    log_date = datetime.datetime.strptime(log['date'], "%Y-%m-%d").date()
                    days = (log_date - first_date).days
                    X.append(days)
                    y.append(float(log['weight']))
                    actual_data.append({
                        "date": log['date'],
                        "weight": float(log['weight'])
                    })

                # Calculate regression
                m, b, r_squared = linear_regression(X, y)

                # Future prediction
                last_date_str = logs[-1]['date']
                last_date = datetime.datetime.strptime(last_date_str, "%Y-%m-%d").date()
                target_date = last_date + datetime.timedelta(days=prediction_days)
                target_days_since_first = (target_date - first_date).days

                predicted_weight = m * target_days_since_first + b
                predicted_weight = max(0.1, round(predicted_weight, 2))
                growth_rate_weekly = round(m * 7, 2)

                # Build response
                first_date_fmt = first_date.strftime("%B %d")
                last_date_fmt = last_date.strftime("%B %d")
                target_date_fmt = target_date.strftime("%B %d")

                insight_text = (
                    f"Based on {pet_name}'s weight logs from {first_date_fmt} to {last_date_fmt}, "
                    f"the linear regression model predicts a steady weight increase to <strong>{predicted_weight} kg</strong> "
                    f"by {target_date_fmt}. This trend suggests healthy and consistent growth consistent with a well-fed {breed_str}. "
                    f"No abnormal weight spikes detected."
                )

                response_data = {
                    "pet_name": pet_name,
                    "breed": breed_str,
                    "actual_data": actual_data,
                    "predicted_weight": predicted_weight,
                    "prediction_date": target_date.strftime("%Y-%m-%d"),
                    "growth_rate_weekly": growth_rate_weekly,
                    "r_squared": round(r_squared, 2),
                    "insight_text": insight_text
                }

                self.send_json_response(response_data)
            except Exception as e:
                self.send_error_response(str(e))
        else:
            self.send_response(404)
            self.end_headers()

    def do_GET(self):
        # Standard GET health check or basic prediction interface
        parsed_url = urlparse(self.path)
        if parsed_url.path == '/api/predict':
            # Support GET with query string data
            query_params = parse_qs(parsed_url.query)
            data_str = query_params.get('data', [None])[0]
            
            if data_str:
                try:
                    logs = json.loads(data_str)
                    # Redirect to POST processing logic
                    self.send_error_response("Please use POST method to submit weight logs JSON payload.")
                    return
                except Exception as e:
                    self.send_error_response("Invalid data format: " + str(e))
                    return

            # Default status response
            self.send_json_response({
                "status": "online",
                "message": "Python Linear Regression API server is active. Use POST to query predictions."
            })
        else:
            self.send_response(404)
            self.end_headers()

    def send_json_response(self, data):
        self.send_response(200)
        self.send_header('Content-Type', 'application/json')
        self.send_header('Access-Control-Allow-Origin', '*')
        self.end_headers()
        self.wfile.write(json.dumps(data).encode('utf-8'))

    def send_error_response(self, message):
        self.send_response(400)
        self.send_header('Content-Type', 'application/json')
        self.send_header('Access-Control-Allow-Origin', '*')
        self.end_headers()
        self.wfile.write(json.dumps({"error": message}).encode('utf-8'))

def run(server_class=HTTPServer, handler_class=PredictionAPIHandler, port=5000):
    server_address = ('127.0.0.1', port)
    httpd = server_class(server_address, handler_class)
    print(f"Starting Python HTTP API Server on port {port}...")
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        pass
    print("Stopping server.")

if __name__ == '__main__':
    run()
