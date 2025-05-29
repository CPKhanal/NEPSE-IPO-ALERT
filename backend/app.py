from flask import Flask, jsonify
from flask_cors import CORS
import requests
from bs4 import BeautifulSoup
import os

app = Flask(__name__)
CORS(app)

def fetch_ipo_list():
    url = "https://cdsc.com.np/ipolist"
    response = requests.get(url)
    soup = BeautifulSoup(response.text, 'html.parser')
    
    table = soup.find('table')
    headers = [th.get_text(strip=True) for th in table.find('thead').find_all('th')]

    ipo_list = []
    for row in table.find('tbody').find_all('tr'):
        cols = [td.get_text(strip=True) for td in row.find_all('td')]
        ipo = dict(zip(headers, cols))
        ipo_list.append(ipo)

    return ipo_list

@app.route('/api/ipo-listings', methods=['GET'])
def ipo_listings():
    try:
        data = fetch_ipo_list()
        return jsonify(data)
    except Exception as e:
        return jsonify({"error": str(e)}), 500

# 🟡 THIS PART IS THE FIX
if __name__ == '__main__':
    port = int(os.environ.get("PORT", 5000))  # Use Render's dynamic port
    app.run(host='0.0.0.0', port=port)        # Bind to 0.0.0.0 for public access
