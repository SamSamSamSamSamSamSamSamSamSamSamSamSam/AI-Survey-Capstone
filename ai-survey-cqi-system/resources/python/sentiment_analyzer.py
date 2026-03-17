import sys
import json
import logging
from typing import List, Dict, Any, Literal

from pydantic import BaseModel, Field, RootModel

from google import genai
from google.genai import types
from google.genai.errors import APIError

logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

MODEL_NAME = "gemini-2.5-flash" 

class SentimentResult(BaseModel):
    id: int = Field(description="The original response ID.")
    sentiment_label: Literal["positive", "negative", "neutral"] = Field(
        description="The determined sentiment: positive, negative, or neutral."
    )
    sentiment_score: float = Field(
        description="Fixed score of 1.0 for a definitive label.",
        default=1.0
    )


class SentimentBatch(RootModel):
    root: List[SentimentResult]

try:
    client = genai.Client()
    logging.info(f"Gemini Client initialized with model: {MODEL_NAME}")
except Exception as e:
    logging.error(f"Failed to initialize Gemini Client. Check GEMINI_API_KEY environment variable: {e}")
    sys.exit(1)

RESPONSE_SCHEMA = SentimentBatch.model_json_schema()

SYSTEM_INSTRUCTION = (
    "You are an expert sentiment analysis engine. Your task is to analyze a list of user responses "
    "and classify the sentiment of each one as either 'positive', 'negative', or 'neutral'. "
    "You MUST return the output as a single JSON array object that strictly adheres to the provided schema. "
    "Set the 'sentiment_score' to 1.0 for every result."
)

def analyze_responses(responses: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
    if not responses:
        return []

    responses_for_prompt = [{"id": r.get("id", i), "text": r.get("text", "")} 
                            for i, r in enumerate(responses)]
    
    user_prompt = (
        "Analyze the sentiment for the following list of text responses:\n\n"
        f"{json.dumps(responses_for_prompt, indent=2)}"
    )

    logging.info(f"Sending {len(responses)} responses for analysis to {MODEL_NAME}...")

    try:
        response = client.models.generate_content(
            model=MODEL_NAME,
            contents=[user_prompt],
            config=types.GenerateContentConfig(
                system_instruction=SYSTEM_INSTRUCTION,
                response_mime_type="application/json", 
                response_schema=RESPONSE_SCHEMA,
            )
        )
        
        raw_json = response.text
        
        parsed_data = SentimentBatch.model_validate_json(raw_json)
        
        results_list = parsed_data.root 
        analyzed_results = [item.model_dump() for item in results_list]
        
        logging.info("Analysis complete and structured JSON received.")
        
        return analyzed_results 

    except APIError as e:
        logging.error(f"Gemini API call failed: {e}")
        return [
            {"id": r.get("id", i), "sentiment_label": "api_error", "sentiment_score": 0.0}
            for i, r in enumerate(responses)
        ]
    except Exception as e:
        raw_output = getattr(locals().get('response'), 'text', 'N/A')
        logging.error(f"Pydantic validation or JSON decode failed: {e}. Raw output: {raw_output}")
        return [
            {"id": r.get("id", i), "sentiment_label": "parse_error", "sentiment_score": 0.0}
            for i, r in enumerate(responses)
        ]

if __name__ == "__main__":
    raw = sys.stdin.read() # Read from stdin when run by Laravel
    
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
            {"id": 5, "text": "This is great!"}
        ]

        test_results = analyze_responses(test_data)
        print(json.dumps(test_results, indent=2))
        sys.stdout.flush()
        sys.exit(0)
