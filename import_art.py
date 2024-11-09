# save this as import_art.py
import json
import mysql.connector

def import_json_to_mysql(json_file_path):
    # Update these connection details to match your MySQL settings
    db_config = {
        'host': 'localhost',
        'user': 'root',  # Replace with your MySQL username
        'password': '',  # Replace with your MySQL password
        'database': 'smart'  # This should match your database name
    }

    try:
        # Read JSON file
        with open("./raw_data.json", 'r') as file:
            data = json.load(file)

        # Connect to MySQL
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()

        # Prepare insert statement
        insert_query = """
        INSERT INTO Artworks (
            user_id, title, latitude, longitude, photo_url, artist_name, creation_year
        ) VALUES (%s, %s, %s, %s, %s, %s, %s)
        """

        # Process each artwork
        for artwork in data:
            values = (
                1,  # default user_id
                artwork['list_title'],
                float(artwork['latitude']),
                float(artwork['longitude']),
                artwork['photo_webs'],
                artwork['artist_ful'],
                artwork['art_year']
            )
            
            cursor.execute(insert_query, values)

        # Commit changes
        conn.commit()
        print("Data imported successfully!")

    except mysql.connector.Error as err:
        print(f"Database error: {err}")
    except Exception as e:
        print(f"Error: {e}")
    finally:
        if 'conn' in locals() and conn.is_connected():
            cursor.close()
            conn.close()
            print("Database connection closed.")

if __name__ == "__main__":
    # Replace with the path to your JSON file
    json_file_path = 'art_data.json'
    import_json_to_mysql(json_file_path)