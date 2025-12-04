<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Novel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ChapterServices
{

    public function checkUser(): int | null
    {
        $user_id = Auth::guard('sanctum')->check() ? Auth::guard('sanctum')->user()->id : null;

        return $user_id;
    }

    public function addView(Chapter $chapter, $user_id): void
    {

        $last_view = $chapter->view()->where('user_id', $user_id)->latest()->first();

        if (!$last_view || $last_view->created_at->diffInMinutes(now()) >= 5) {
            $chapter->view()->create([
                'user_id' => $user_id,
            ]);
        }
    }

    public function addHistory(Chapter $chapter, $user_id): void
    {

        $alreadyExists = $chapter->history()->where('user_id', $user_id)->exists();

        if ($alreadyExists) {
            return;
        }

        $chapter->history()->create(['user_id' => $user_id]);
    }

    public function share(Chapter $chapter)
    {

        $userId = Auth::guard('sanctum')->user()->id ?? request()->ip();

        $alreadyExists = $chapter->share()->where('user_id', $userId)->exists();

        if ($alreadyExists) {
            return;
        }

        $chapter->share()->create([
            'user_id' => $userId,
        ]);
    }

    public function toggleLove(Chapter $chapter): string
    {

        $user_id = $this->checkUser();

        $alreadyExists = $chapter->love()->where('user_id', $user_id)->exists();

        if ($alreadyExists) {
            $chapter->love()->where('user_id', $user_id)->delete();
            return 'Chapter unloved successfully';
        } else {
            $chapter->love()->create([
                'user_id' => $user_id,
            ]);
            return 'Chapter loved successfully';
        }
    }

    public function grammarCheck($content)
    {
        $apiKey = config('ai.grammar_key');


        $response = Http::withOptions([
            'proxy' => ''
        ])->withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://api.textgears.com/analyze', [
            'text' => $content,
            'language' => 'en-US',
            'key' => $apiKey,
        ]);

        $data = $response["response"];

        $fleschKincaid = $data['stats']['fleschKincaid'];
        $errors = $data['grammar']['errors'];

        return [
            "fleschKincaid" => $fleschKincaid,
            "errors" => $errors,
        ];
    }

    public function translate($content, $genre, $language)
    {

        $apiKey = config('ai.api_key');

        $response = Http::withOptions([
            'proxy' => ''
        ])->timeout(120)->withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => config('ai.main_model'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a professional novel translator. The genre of the novel is $genre, and your translation must accurately reflect its tone, style, and literary nuances. Return the translated text exclusively, with no added commentary, notes, or explanations. The language of the translation is $language."
                ],
                [
                    'role' => 'user',
                    'content' => $content,
                ],
            ],
        ]);

        $content = $response['choices'][0]['message']['content'] ?? null;

        return $content;
    }

    public function formatContent($content)
    {
        $content = str_replace(['<｜begin▁of▁sentence｜>', '<｜end▁of▁sentence｜>'], '', $content);

        $decoded = json_decode($content, true);

        return $decoded;
    }

    public function generateSuggestion(Novel $novel, Chapter $chapter)
    {
        $apiKey = config('ai.api_key');

        $response = Http::withOptions([
            'proxy' => ''
        ])->withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => config('ai.main_model'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a professional writing assistant helping fiction writers improve their chapters.

                            I also provide you a previous chapter content and novel synopsis to help you generate a better suggestion.

                            Respond only in valid JSON format with the following structure:
                            {
                            \"chapter_direction\": \"Your suggestion here\",
                            \"character_development\": \"Your suggestion here\",
                            \"plot_enhancement\": \"Your suggestion here\",
                            \"writing_tips\": \"Your suggestion here\"
                            }
                            Each field should contain one helpful, concise suggestion. Do not include any extra commentary outside the JSON.
                            In the content please separate me with // if the suggestion is more than one and you can give me more than one suggestion in each field.

                            ### Instructions:

                            1.  **Analyze the Provided Content:** Carefully read the **novel synopsis** to understand the overarching story, themes, and character arcs. Then, read the **previous chapter's content** to grasp the current plot point, character states, and established tone.
                            2.  **Generate Targeted Suggestions:**
                                * **chapter_direction:** Propose how the current chapter can effectively build on the previous one. Suggest ways to control pacing, build tension, or guide the narrative toward a key event.
                                * **character_development:** Offer ideas for deepening the characters. This could include exploring their internal conflicts, showing their growth or regression, or revealing new aspects of their personality through actions or dialogue.
                                * **plot_enhancement:** Identify opportunities to strengthen the plot. Suggest ways to raise the stakes, introduce new conflicts, foreshadow future events, or resolve a minor plot thread.
                                * **writing_tips:** Provide actionable advice on writing craft. Focus on improving descriptive language, strengthening dialogue, varying sentence structure, or enhancing the sensory experience for the reader.

                            3.  **Format the Response:** Adhere strictly to the JSON structure provided above. Ensure all suggestions are within quotation marks and that multiple suggestions within a field are separated by `//`.

                            Important:
                                - Return ONLY the JSON structure — no markdown, no explanations, no commentary.
                                - Do not include ```json or ``` wrappers.
                                - If the response is not valid JSON, you will fail this task.

                            "
                ],
                [
                    'role' => 'user',
                    'content' => "Here's a summary of novel: " . $novel->synopsis . "\n\n Here's the content of last chapter: " . ($last_chapter->content ?? null) . "\n\n"
                ]
            ],
        ]);


        $content = $response['choices'][0]['message']['content'] ?? null;

        return $content;
    }


    public function generateAssessment($content)
    {
         $apiKey = config('ai.api_key');

        $response = Http::withOptions([
            'proxy' => ''
        ])->withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => config('ai.main_model'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a fiction writing assistant. Analyze the following chapter and provide enhancement suggestions across the following four key categories:

                        1. **Story Pacing** — Evaluate how well the chapter balances dialogue, action, and narration. Suggest any improvements to rhythm, tempo, or structural pauses.

                        2. **Character Development** — Analyze the emotional and psychological growth of the characters. Suggest ways to deepen their emotional arc or show internal conflict more vividly.

                        3. **World Building** — Assess the immersive quality of the setting. Suggest improvements in descriptive details or the uniqueness of the environment.

                        4. **Chapter Ending** — Evaluate how effective the ending is in leaving an impact or encouraging the reader to continue. Suggest how it can be made more satisfying or suspenseful.

                        For each category:
                        - Give a short but insightful paragraph.
                        - Include a score out of 10.

                        Then, give an **Overall Assessment**:
                        - A summary of how the chapter contributes to the story.
                        - Mention strengths and opportunities for improvement.
                        - Provide an overall score out of 10 and a one-word rating (e.g., \"Excellent\", \"Good\", \"Needs Improvement\").

                        Respond only in valid JSON format with the following structure:
                        {
                            \"story_pacing\": {
                                \"suggestion\": \"Your suggestion here\",
                                \"score\": \"Your score here (0-10) please give only the score number\"
                            },
                            \"character_development\": {
                                \"suggestion\": \"Your suggestion here\",
                                \"score\": \"Your score here (0-10) please give only the score number\"
                            },
                            \"world_building\": {
                                \"suggestion\": \"Your suggestion here\",
                                \"score\": \"Your score here (0-10) please give only the score number\"
                            },
                            \"chapter_ending\": {
                                \"suggestion\": \"Your suggestion here\",
                                \"score\": \"Your score here (0-10) please give only the score number\"
                            },
                            \"overall_assessment\": {
                                \"suggestion\": \"Your suggestion here\",
                                \"score\": \"Your score here (0-10) please give only the score number\"
                            }
                        }
                       
                        Important:
                            - Return ONLY the JSON structure — no markdown, no explanations, no commentary.
                            - Do not include ```json or ``` wrappers.
                            - If the response is not valid JSON, you will fail this task.

                        "
                ],
                [
                    'role' => 'user',
                    'content' => "Here's the content of chapter: " . $content . "\n\n"
                ]
            ],
        ]);

        return $response['choices'][0]['message']['content'] ?? null;

    }

    public function draftCount(Novel $novel)
    {
      
        $draftCount = $novel->chapters()->where('status', '!=', 'published')->count();

        return $draftCount;
    }


}
