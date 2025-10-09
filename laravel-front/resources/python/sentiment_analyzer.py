# sentiment_analyzer.py
from transformers import pipeline
import sys, json

# Load sentiment analysis pipeline
sentiment_pipeline = pipeline("sentiment-analysis")

def analyze_responses(responses):
    """
    responses: list of dictionaries [{"id":1,"text":"..."}, ...]
    returns: list of dictionaries [{"id":1,"label":"POSITIVE","score":0.99}, ...]
    """
    results = []
    for r in responses:
        try:
            output = sentiment_pipeline(r["text"])[0]
            results.append({
                "id": r["id"],
                "sentiment_label": output["label"].lower(),  # positive/negative/neutral
                "sentiment_score": output["score"]
            })
        except Exception as e:
            results.append({
                "id": r["id"],
                "sentiment_label": "error",
                "sentiment_score": 0
            })
    return results

if __name__ == "__main__":
    # Read JSON input from Laravel
    input_data = json.loads(sys.stdin.read())
    analyzed = analyze_responses(input_data)
    # Output JSON to Laravel
    print(json.dumps(analyzed))
    sys.stdout.flush()  
