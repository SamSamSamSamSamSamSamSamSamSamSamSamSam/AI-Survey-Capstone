from transformers import pipeline
import sys
import json
import logging
from typing import List, Dict, Any

# Configure basic logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# --- CONFIGURATION ---
MODEL_NAME = "lxyuan/distilbert-base-multilingual-cased-sentiments-student"

try:
    # Note: Use "sentiment-analysis" for the pipeline type, not "text-classification" 
    # if you want standard sentiment output structure.
    sentiment_pipeline = pipeline("sentiment-analysis", model=MODEL_NAME)
    logging.info(f"Pipeline loaded with model: {MODEL_NAME}")
except Exception as e:
    logging.error(f"Failed to load pipeline: {e}")
    sys.exit(1)


def analyze_responses(responses: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
    """
    Analyzes a list of responses efficiently using batch processing.
    """
    if not responses:
        return []

    # 1. Extract all text inputs
    texts = [r.get("text", "") for r in responses]

    # 2. Process the entire batch at once
    try:
        outputs = sentiment_pipeline(texts)
    except Exception as e:
        logging.error(f"Batch sentiment analysis failed: {e}")
        return [
            {"id": r.get("id", "unknown"), "sentiment_label": "error", "sentiment_score": 0}
            for r in responses
        ]

    # 3. Combine results with original IDs
    results = []
    for i, output in enumerate(outputs):
        r = responses[i]

        response_id = r.get("id", f"missing_id_{i}")

        # The output structure is typically a list of one dict: [{'label': 'neutral', 'score': 0.99}]
        sentiment_info = output[0] if isinstance(output, list) else output

        # Ensure label is lowercase for database consistency
        sentiment_label = sentiment_info.get("label", "unknown").lower()
        sentiment_score = sentiment_info.get("score", 0.0)

        results.append({
            "id": response_id,
            "sentiment_label": sentiment_label,
            "sentiment_score": sentiment_score
        })

    return results


if __name__ == "__main__":
    # Read JSON input from stdin (e.g., from Laravel)
    
    # --- TEMPORARY TEST MODE ACTIVATION ---
    # FOR TESTING: Set raw="" to immediately run the 'else' block with test data.
    # When ready for Laravel, change this line to: raw = sys.stdin.read()
    raw = sys.stdin.read()
    # -------------------------------------
    
    # Uncomment this when integrating with Laravel:
    # raw = sys.stdin.read() 

    if raw and raw.strip():
        # --- Handle Stdin Input (Laravel Execution) ---
        try:
            input_data = json.loads(raw)
            if not isinstance(input_data, list):
                 raise TypeError("JSON input must be a list of objects.")

        except json.JSONDecodeError:
            logging.error("Failed to decode JSON from stdin.")
            print(json.dumps([]))
            sys.stdout.flush()
            sys.exit(1)
        except TypeError as e:
            logging.error(f"Invalid input type: {e}")
            print(json.dumps([]))
            sys.stdout.flush()
            sys.exit(1)

        # Process the valid input data
        analyzed = analyze_responses(input_data)
        print(json.dumps(analyzed))
        sys.stdout.flush()
        sys.exit(0)

    else:
        # --- Handle No Stdin (Direct Execution / Test Data) ---
        logging.info("No stdin data provided. Using test data.")
        test_data = [
            {"id": 1, "text": "I love this course and would take it again!"},
            {"id": 2, "text": "The teacher is boring and the material is old."},
            {"id": 3, "text": "It was just okay, nothing special, nothing bad."},
            {"id": 4, "text": "I can't say if I liked it or not."},
            {"id": 5, "text": "This is great!"}
        ]

        test_results = analyze_responses(test_data)
        print(json.dumps(test_results, indent=2))
        sys.stdout.flush()
        sys.exit(0)