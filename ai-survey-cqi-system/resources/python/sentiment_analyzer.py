import sys
import json
import logging
from typing import List, Dict, Any

import torch
from transformers import AutoTokenizer, AutoModelForSequenceClassification

logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Path to your fine-tuned DistilBERT model
MODEL_PATH = "resources/python/model"

# Label mapping from your model's config.json
ID2LABEL = {
    0: "negative",
    1: "neutral",
    2: "positive",
}

# Load model and tokenizer once at startup
try:
    logging.info(f"Loading tokenizer from {MODEL_PATH}...")
    tokenizer = AutoTokenizer.from_pretrained(MODEL_PATH)

    logging.info(f"Loading model from {MODEL_PATH}...")
    model = AutoModelForSequenceClassification.from_pretrained(MODEL_PATH)
    model.eval()

    logging.info("Model and tokenizer loaded successfully.")
except Exception as e:
    logging.error(f"Failed to load model or tokenizer from '{MODEL_PATH}': {e}")
    sys.exit(1)


def predict_sentiment(text: str) -> Dict[str, Any]:
    """Run inference on a single text and return label + confidence score."""
    inputs = tokenizer(
        text,
        return_tensors="pt",
        truncation=True,
        padding=True,
        max_length=512,
    )

    with torch.no_grad():
        outputs = model(**inputs)

    logits = outputs.logits
    probabilities = torch.softmax(logits, dim=-1).squeeze()
    predicted_class = torch.argmax(probabilities).item()

    label = ID2LABEL[predicted_class]
    score = round(probabilities[predicted_class].item(), 4)

    return {"sentiment_label": label, "sentiment_score": score}


def analyze_responses(responses: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
    """Analyze sentiment for a list of response dicts with 'id' and 'text' keys."""
    if not responses:
        return []

    results = []
    logging.info(f"Analyzing {len(responses)} responses with local DistilBERT model...")

    for i, r in enumerate(responses):
        response_id = r.get("id", i)
        text = r.get("text", "").strip()

        if not text:
            # Empty text — default to neutral
            results.append({
                "id": response_id,
                "sentiment_label": "neutral",
                "sentiment_score": 1.0,
            })
            continue

        try:
            prediction = predict_sentiment(text)
            results.append({
                "id": response_id,
                "sentiment_label": prediction["sentiment_label"],
                "sentiment_score": prediction["sentiment_score"],
            })
        except Exception as e:
            logging.error(f"Inference failed for id={response_id}: {e}")
            results.append({
                "id": response_id,
                "sentiment_label": "parse_error",
                "sentiment_score": 0.0,
            })

    logging.info("Analysis complete.")
    return results


if __name__ == "__main__":
    raw = sys.stdin.read()

    if raw and raw.strip():
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

        analyzed = analyze_responses(input_data)
        print(json.dumps(analyzed))
        sys.stdout.flush()
        sys.exit(0)

    else:
        logging.info("No stdin data provided. Using test data.")

        test_data = [
            {"id": 1, "text": "I love this course and would take it again!"},
            {"id": 2, "text": "The teacher is boring and the material is old."},
            {"id": 3, "text": "It was just okay, nothing special, nothing bad."},
            {"id": 4, "text": "I can't say if I liked it or not."},
            {"id": 5, "text": "This is great!"},
        ]

        test_results = analyze_responses(test_data)
        print(json.dumps(test_results, indent=2))
        sys.stdout.flush()
        sys.exit(0)
