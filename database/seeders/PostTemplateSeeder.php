<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostTemplate;
use Illuminate\Support\Facades\DB;

class PostTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing templates
        PostTemplate::truncate();

        $templates = [
            // ===== SUCCESS STORY TEMPLATES (High Engagement) =====
            [
                'title' => 'Rags to Riches Story',
                'content' => "I {past_negative_situation}.\n\nEveryone told me {common_objection}.\n\nBut I decided to {your_action}.\n\nHere's what happened:\n\n→ {result_1}\n→ {result_2}\n→ {result_3}\n\nThe lesson?\n{key_insight}\n\nWhat's one obstacle you're facing right now? 👇",
                'category' => 'story',
                'industry' => 'general',
                'engagement_score' => 92,
                'variables' => json_encode(['past_negative_situation', 'common_objection', 'your_action', 'result_1', 'result_2', 'result_3', 'key_insight']),
                'description' => 'Classic success story format - highly engaging'
            ],
            [
                'title' => 'Failure to Success Journey',
                'content' => "I failed at {topic} 3 times before I got it right.\n\nHere's what I learned:\n\n❌ Attempt 1: {failure_1}\n❌ Attempt 2: {failure_2}\n❌ Attempt 3: {failure_3}\n\n✅ What finally worked: {success_formula}\n\nDon't give up on the first try.\n\nYour breakthrough might be one attempt away.\n\n#Success #Persistence #Growth",
                'category' => 'story',
                'industry' => 'entrepreneurship',
                'engagement_score' => 88,
                'variables' => json_encode(['topic', 'failure_1', 'failure_2', 'failure_3', 'success_formula']),
                'description' => 'Vulnerability + lesson = high engagement'
            ],

            // ===== LISTICLE TEMPLATES (Very Popular) =====
            [
                'title' => '5 Lessons I Learned',
                'content' => "5 lessons I wish I knew about {topic} earlier:\n\n1. {lesson_1}\n2. {lesson_2}\n3. {lesson_3}\n4. {lesson_4}\n5. {lesson_5}\n\nWhich one resonates with you most?\n\nSave this for later ↓\n\n#Lessons #Growth #{topic}",
                'category' => 'listicle',
                'industry' => 'general',
                'engagement_score' => 85,
                'variables' => json_encode(['topic', 'lesson_1', 'lesson_2', 'lesson_3', 'lesson_4', 'lesson_5']),
                'description' => 'Classic 5-point list - easy to consume'
            ],
            [
                'title' => '10 Mistakes to Avoid',
                'content' => "10 mistakes most people make with {topic}:\n\n1. {mistake_1}\n2. {mistake_2}\n3. {mistake_3}\n4. {mistake_4}\n5. {mistake_5}\n6. {mistake_6}\n7. {mistake_7}\n8. {mistake_8}\n9. {mistake_9}\n10. {mistake_10}\n\nWhich one are you guilty of?\n\nBe honest 👇\n\n#{topic} #Mistakes #Lessons",
                'category' => 'listicle',
                'industry' => 'general',
                'engagement_score' => 87,
                'variables' => json_encode(['topic', 'mistake_1', 'mistake_2', 'mistake_3', 'mistake_4', 'mistake_5', 'mistake_6', 'mistake_7', 'mistake_8', 'mistake_9', 'mistake_10']),
                'description' => 'Mistakes list - people love learning what NOT to do'
            ],
            [
                'title' => '7 Tools That Changed My Life',
                'content' => "7 {tool_type} that 10x my {outcome}:\n\n1. {tool_1} → {benefit_1}\n2. {tool_2} → {benefit_2}\n3. {tool_3} → {benefit_3}\n4. {tool_4} → {benefit_4}\n5. {tool_5} → {benefit_5}\n6. {tool_6} → {benefit_6}\n7. {tool_7} → {benefit_7}\n\nBookmark this. You'll thank me later.\n\nWhich one will you try first?\n\n#Tools #Productivity #Resources",
                'category' => 'listicle',
                'industry' => 'tech',
                'engagement_score' => 90,
                'variables' => json_encode(['tool_type', 'outcome', 'tool_1', 'benefit_1', 'tool_2', 'benefit_2', 'tool_3', 'benefit_3', 'tool_4', 'benefit_4', 'tool_5', 'benefit_5', 'tool_6', 'benefit_6', 'tool_7', 'benefit_7']),
                'description' => 'Resource list with benefits - very shareable'
            ],

            // ===== HOW-TO TEMPLATES =====
            [
                'title' => 'How to Achieve X in Y Time',
                'content' => "How to {achieve_goal} in {timeframe}:\n\nMost people make it complicated.\n\nHere's the simple 3-step process I use:\n\n━━━━━━━━━━━━━\nStep 1: {step_1_name}\n→ {step_1_description}\n→ Why it works: {step_1_benefit}\n\nStep 2: {step_2_name}\n→ {step_2_description}\n→ Why it works: {step_2_benefit}\n\nStep 3: {step_3_name}\n→ {step_3_description}\n→ Why it works: {step_3_benefit}\n━━━━━━━━━━━━━\n\nThe result? {final_outcome}\n\nSave this for later.\n\n#{achieve_goal} #HowTo #Tips",
                'category' => 'tip',
                'industry' => 'general',
                'engagement_score' => 89,
                'variables' => json_encode(['achieve_goal', 'timeframe', 'step_1_name', 'step_1_description', 'step_1_benefit', 'step_2_name', 'step_2_description', 'step_2_benefit', 'step_3_name', 'step_3_description', 'step_3_benefit', 'final_outcome']),
                'description' => 'Step-by-step guide with benefits'
            ],
            [
                'title' => 'The Simple Framework',
                'content' => "The {framework_name} framework for {goal}:\n\nI used this to {your_result}.\n\nHere's how it works:\n\n🔹 Part 1: {part_1}\n{part_1_detail}\n\n🔹 Part 2: {part_2}\n{part_2_detail}\n\n🔹 Part 3: {part_3}\n{part_3_detail}\n\n🔹 Part 4: {part_4}\n{part_4_detail}\n\nSimple, right?\n\nTry it this week and let me know how it goes.\n\n#Framework #Strategy #{goal}",
                'category' => 'tip',
                'industry' => 'business',
                'engagement_score' => 86,
                'variables' => json_encode(['framework_name', 'goal', 'your_result', 'part_1', 'part_1_detail', 'part_2', 'part_2_detail', 'part_3', 'part_3_detail', 'part_4', 'part_4_detail']),
                'description' => 'Framework/system template - actionable'
            ],

            // ===== MYTH-BUSTING / CONTROVERSIAL =====
            [
                'title' => 'Myth vs Reality',
                'content' => "The biggest myth about {topic}:\n\n❌ Myth: {common_myth}\n\n✅ Reality: {actual_truth}\n\nHere's why this matters:\n\n{explanation}\n\nThe data shows:\n→ {data_point_1}\n→ {data_point_2}\n→ {data_point_3}\n\nDon't fall for the myth.\n\n{call_to_action}\n\n#MythBusting #Truth #{topic}",
                'category' => 'controversial',
                'industry' => 'general',
                'engagement_score' => 93,
                'variables' => json_encode(['topic', 'common_myth', 'actual_truth', 'explanation', 'data_point_1', 'data_point_2', 'data_point_3', 'call_to_action']),
                'description' => 'Myth-busting format - sparks debate'
            ],
            [
                'title' => 'Unpopular Opinion',
                'content' => "Unpopular opinion about {topic}:\n\n{controversial_statement}\n\nI know this goes against what everyone says.\n\nBut hear me out:\n\n→ {reason_1}\n→ {reason_2}\n→ {reason_3}\n\nThe data backs this up:\n{supporting_evidence}\n\nAm I crazy, or am I onto something?\n\nAgree or disagree? Let's debate 👇\n\n#UnpopularOpinion #Debate #{topic}",
                'category' => 'controversial',
                'industry' => 'general',
                'engagement_score' => 95,
                'variables' => json_encode(['topic', 'controversial_statement', 'reason_1', 'reason_2', 'reason_3', 'supporting_evidence']),
                'description' => 'Unpopular opinion - highest engagement'
            ],
            [
                'title' => 'Everyone is Wrong About This',
                'content' => "Everyone is wrong about {topic}.\n\nHere's what they don't tell you:\n\n{hidden_truth}\n\nI spent {time_invested} figuring this out.\n\nThe real secret?\n\n1. {secret_1}\n2. {secret_2}\n3. {secret_3}\n\nThis changed everything for me.\n\nDon't make the same mistakes I did.\n\nWhat's your take? 👇\n\n#{topic} #Truth #RealTalk",
                'category' => 'controversial',
                'industry' => 'general',
                'engagement_score' => 91,
                'variables' => json_encode(['topic', 'hidden_truth', 'time_invested', 'secret_1', 'secret_2', 'secret_3']),
                'description' => 'Contrarian take - gets attention'
            ],

            // ===== QUESTION POSTS (Engagement Bait) =====
            [
                'title' => 'This or That Question',
                'content' => "Quick question:\n\nWould you rather {option_a} or {option_b}?\n\nMost people choose {common_choice}.\n\nBut here's the thing:\n\n{insight_or_twist}\n\n{additional_context}\n\nWhat would you choose and why?\n\nDrop a comment 👇\n\n#Question #Discussion #{topic}",
                'category' => 'question',
                'industry' => 'general',
                'engagement_score' => 84,
                'variables' => json_encode(['option_a', 'option_b', 'common_choice', 'insight_or_twist', 'additional_context', 'topic']),
                'description' => 'Binary choice question - drives comments'
            ],
            [
                'title' => 'Hot Take Question',
                'content' => "Hot take:\n\n{provocative_question}\n\nI've seen both sides.\n\nPro {option_1}:\n{pro_argument}\n\nPro {option_2}:\n{pro_argument_2}\n\nMy verdict:\n{your_position}\n\nWhat do you think?\n\nComment below 👇\n\n#{topic} #Debate #HotTake",
                'category' => 'question',
                'industry' => 'general',
                'engagement_score' => 88,
                'variables' => json_encode(['provocative_question', 'option_1', 'pro_argument', 'option_2', 'pro_argument_2', 'your_position', 'topic']),
                'description' => 'Hot take with both sides - balanced debate'
            ],

            // ===== BEHIND-THE-SCENES =====
            [
                'title' => 'What They Don\'t See',
                'content' => "Behind the scenes of {achievement}:\n\nWhat people see:\n✨ {public_perception}\n\nWhat they don't see:\n• {struggle_1}\n• {struggle_2}\n• {struggle_3}\n• {struggle_4}\n\nThe truth about success:\n\n{reality_check}\n\nIt's messy. It's hard. It's worth it.\n\nHere's what I learned:\n{key_lesson}\n\nWho else can relate? 👇\n\n#BehindTheScenes #RealTalk #{topic}",
                'category' => 'behind_scenes',
                'industry' => 'entrepreneurship',
                'engagement_score' => 87,
                'variables' => json_encode(['achievement', 'public_perception', 'struggle_1', 'struggle_2', 'struggle_3', 'struggle_4', 'reality_check', 'key_lesson', 'topic']),
                'description' => 'Vulnerability post - builds connection'
            ],
            [
                'title' => 'A Day in My Life',
                'content' => "A day in the life of {your_role}:\n\n6:00 AM - {morning_routine}\n8:00 AM - {task_1}\n10:00 AM - {task_2}\n12:00 PM - {task_3}\n2:00 PM - {task_4}\n4:00 PM - {task_5}\n6:00 PM - {evening_routine}\n\nThe biggest surprise?\n{surprising_insight}\n\nWhat does your day look like?\n\n#{your_role} #DayInTheLife #Routine",
                'category' => 'behind_scenes',
                'industry' => 'general',
                'engagement_score' => 82,
                'variables' => json_encode(['your_role', 'morning_routine', 'task_1', 'task_2', 'task_3', 'task_4', 'task_5', 'evening_routine', 'surprising_insight']),
                'description' => 'Day in the life - relatable content'
            ],

            // ===== CASE STUDY TEMPLATES =====
            [
                'title' => 'Client Success Case Study',
                'content' => "Case Study: How {client_name} achieved {impressive_result} in {timeframe}\n\nThe Challenge:\n{problem_description}\n\nTheir situation:\n• {pain_point_1}\n• {pain_point_2}\n• {pain_point_3}\n\nThe Strategy:\n1. {strategy_1}\n2. {strategy_2}\n3. {strategy_3}\n\nThe Results:\n✅ {result_1}\n✅ {result_2}\n✅ {result_3}\n\nKey Takeaway:\n{main_lesson}\n\nWant similar results? {cta}\n\n#CaseStudy #Success #Results",
                'category' => 'value_drop',
                'industry' => 'marketing',
                'engagement_score' => 89,
                'variables' => json_encode(['client_name', 'impressive_result', 'timeframe', 'problem_description', 'pain_point_1', 'pain_point_2', 'pain_point_3', 'strategy_1', 'strategy_2', 'strategy_3', 'result_1', 'result_2', 'result_3', 'main_lesson', 'cta']),
                'description' => 'Case study with proof - builds credibility'
            ],
            [
                'title' => 'Before & After Transformation',
                'content' => "The transformation:\n\nBEFORE:\n❌ {before_state_1}\n❌ {before_state_2}\n❌ {before_state_3}\n\n↓ What changed: {intervention}\n\nAFTER:\n✅ {after_state_1}\n✅ {after_state_2}\n✅ {after_state_3}\n\nTime frame: {duration}\nCost: {investment}\nResult: {roi}\n\nThe secret?\n{key_strategy}\n\nReady to transform? {cta}\n\n#Transformation #BeforeAfter #Results",
                'category' => 'value_drop',
                'industry' => 'general',
                'engagement_score' => 90,
                'variables' => json_encode(['before_state_1', 'before_state_2', 'before_state_3', 'intervention', 'after_state_1', 'after_state_2', 'after_state_3', 'duration', 'investment', 'roi', 'key_strategy', 'cta']),
                'description' => 'Before/after - visual progress'
            ],

            // ===== COMPARISON POSTS =====
            [
                'title' => 'X vs Y Comparison',
                'content' => "{option_a} vs {option_b} for {use_case}\n\nI tested both for {test_period}.\n\nHere's my honest breakdown:\n\n{option_a}:\n✅ {pro_1}\n✅ {pro_2}\n❌ {con_1}\n❌ {con_2}\n\n{option_b}:\n✅ {pro_3}\n✅ {pro_4}\n❌ {con_3}\n❌ {con_4}\n\nMy verdict:\n{conclusion}\n\nCost: {cost_comparison}\nEase: {ease_comparison}\nResults: {results_comparison}\n\nWhat's your experience?\n\n#Comparison #Review #{topic}",
                'category' => 'value_drop',
                'industry' => 'tech',
                'engagement_score' => 85,
                'variables' => json_encode(['option_a', 'option_b', 'use_case', 'test_period', 'pro_1', 'pro_2', 'con_1', 'con_2', 'pro_3', 'pro_4', 'con_3', 'con_4', 'conclusion', 'cost_comparison', 'ease_comparison', 'results_comparison', 'topic']),
                'description' => 'Side-by-side comparison - helpful'
            ],

            // ===== PERSONAL LESSON TEMPLATES =====
            [
                'title' => 'What I Wish I Knew at 25',
                'content' => "What I wish I knew about {topic} at {age}:\n\n→ {lesson_1}\n\nI learned this the hard way: {story_1}\n\n→ {lesson_2}\n\nThis cost me: {cost_1}\n\n→ {lesson_3}\n\nThis saved me: {benefit_1}\n\n→ {lesson_4}\n\nNobody tells you: {hidden_truth}\n\nIf I could go back, I'd tell myself:\n\n{advice_to_younger_self}\n\nWhat would you tell your younger self?\n\n#Lessons #Wisdom #Growth",
                'category' => 'story',
                'industry' => 'general',
                'engagement_score' => 86,
                'variables' => json_encode(['topic', 'age', 'lesson_1', 'story_1', 'lesson_2', 'cost_1', 'lesson_3', 'benefit_1', 'lesson_4', 'hidden_truth', 'advice_to_younger_self']),
                'description' => 'Reflective wisdom - relatable'
            ],
            [
                'title' => 'My Biggest Mistakes',
                'content' => "My 3 biggest mistakes in {field}:\n\n━━━━━━━━━━━━━\nMistake #1: {mistake_1}\n\nWhat happened:\n{consequence_1}\n\nWhat I learned:\n{lesson_1}\n\n━━━━━━━━━━━━━\nMistake #2: {mistake_2}\n\nWhat happened:\n{consequence_2}\n\nWhat I learned:\n{lesson_2}\n\n━━━━━━━━━━━━━\nMistake #3: {mistake_3}\n\nWhat happened:\n{consequence_3}\n\nWhat I learned:\n{lesson_3}\n━━━━━━━━━━━━━\n\nLearn from my mistakes.\n\nDon't repeat them.\n\nWhat mistakes have you made?\n\n#Mistakes #Lessons #{field}",
                'category' => 'story',
                'industry' => 'general',
                'engagement_score' => 88,
                'variables' => json_encode(['field', 'mistake_1', 'consequence_1', 'lesson_1', 'mistake_2', 'consequence_2', 'lesson_2', 'mistake_3', 'consequence_3', 'lesson_3']),
                'description' => 'Mistakes + lessons - very engaging'
            ],

            // ===== QUICK TIP TEMPLATES =====
            [
                'title' => 'One Simple Trick',
                'content' => "One simple trick that {impressive_result}:\n\n{the_trick}\n\nWhy it works:\n{explanation}\n\nHow to do it:\n1. {step_1}\n2. {step_2}\n3. {step_3}\n\nResults you can expect:\n→ {benefit_1}\n→ {benefit_2}\n→ {benefit_3}\n\nTry it this week.\n\nThank me later.\n\n#{topic} #Tip #Hack",
                'category' => 'tip',
                'industry' => 'general',
                'engagement_score' => 83,
                'variables' => json_encode(['impressive_result', 'the_trick', 'explanation', 'step_1', 'step_2', 'step_3', 'benefit_1', 'benefit_2', 'benefit_3', 'topic']),
                'description' => 'Quick win - actionable'
            ],
            [
                'title' => 'The 80/20 Rule for X',
                'content' => "The 80/20 rule for {topic}:\n\n20% of your {input} creates 80% of your {output}.\n\nHere's what to focus on:\n\n🎯 {focus_area_1}\nWhy: {reason_1}\n\n🎯 {focus_area_2}\nWhy: {reason_2}\n\n🎯 {focus_area_3}\nWhy: {reason_3}\n\nWhat to ignore:\n❌ {ignore_1}\n❌ {ignore_2}\n❌ {ignore_3}\n\nWork smarter, not harder.\n\nWhat's your 20%?\n\n#Productivity #8020Rule #{topic}",
                'category' => 'tip',
                'industry' => 'productivity',
                'engagement_score' => 87,
                'variables' => json_encode(['topic', 'input', 'output', 'focus_area_1', 'reason_1', 'focus_area_2', 'reason_2', 'focus_area_3', 'reason_3', 'ignore_1', 'ignore_2', 'ignore_3']),
                'description' => '80/20 principle - strategic focus'
            ],

            // ===== MOTIVATIONAL / INSPIRATIONAL =====
            [
                'title' => 'You Don\'t Need Permission',
                'content' => "You don't need permission to {goal}.\n\nYou don't need:\n❌ {false_requirement_1}\n❌ {false_requirement_2}\n❌ {false_requirement_3}\n\nYou just need:\n✅ {real_requirement_1}\n✅ {real_requirement_2}\n✅ {real_requirement_3}\n\nI started with nothing but {what_you_had}.\n\nNow: {where_you_are}\n\nStop waiting for the perfect moment.\n\n{motivational_message}\n\nStart today.\n\n#Motivation #JustStart #{goal}",
                'category' => 'story',
                'industry' => 'entrepreneurship',
                'engagement_score' => 84,
                'variables' => json_encode(['goal', 'false_requirement_1', 'false_requirement_2', 'false_requirement_3', 'real_requirement_1', 'real_requirement_2', 'real_requirement_3', 'what_you_had', 'where_you_are', 'motivational_message']),
                'description' => 'Motivational permission - empowering'
            ],

            // ===== DATA-DRIVEN POSTS =====
            [
                'title' => 'By The Numbers',
                'content' => "{topic} by the numbers:\n\n📊 {stat_1}\n{stat_1_insight}\n\n📊 {stat_2}\n{stat_2_insight}\n\n📊 {stat_3}\n{stat_3_insight}\n\n📊 {stat_4}\n{stat_4_insight}\n\nWhat this means for you:\n\n{practical_application}\n\nThe bottom line:\n{key_takeaway}\n\nData source: {source}\n\n#Data #Statistics #{topic}",
                'category' => 'value_drop',
                'industry' => 'marketing',
                'engagement_score' => 86,
                'variables' => json_encode(['topic', 'stat_1', 'stat_1_insight', 'stat_2', 'stat_2_insight', 'stat_3', 'stat_3_insight', 'stat_4', 'stat_4_insight', 'practical_application', 'key_takeaway', 'source']),
                'description' => 'Data-driven - authoritative'
            ],

            // ===== ACHIEVEMENT POSTS =====
            [
                'title' => 'Major Milestone Announcement',
                'content' => "I just hit {milestone}! 🎉\n\nA year ago, I was {past_situation}.\n\nToday: {current_situation}\n\nHow I did it:\n\n1️⃣ {key_action_1}\n2️⃣ {key_action_2}\n3️⃣ {key_action_3}\n\nWhat I learned:\n{key_lesson}\n\nNext goal:\n{next_milestone}\n\nThank you to everyone who supported me.\n\nIf I can do it, you can too.\n\n#{topic} #Milestone #Success",
                'category' => 'achievement',
                'industry' => 'general',
                'engagement_score' => 85,
                'variables' => json_encode(['milestone', 'past_situation', 'current_situation', 'key_action_1', 'key_action_2', 'key_action_3', 'key_lesson', 'next_milestone', 'topic']),
                'description' => 'Milestone celebration - inspiring'
            ],

            // ===== CONTRARIAN / THOUGHT LEADERSHIP =====
            [
                'title' => 'Stop Doing This',
                'content' => "Stop {common_practice}.\n\nSeriously. Stop.\n\nHere's why:\n\n{reason_1}\n\nThe data proves it:\n{data_point}\n\nWhat you should do instead:\n\n✓ {alternative_1}\n✓ {alternative_2}\n✓ {alternative_3}\n\nI made this switch {timeframe} ago.\n\nResults:\n→ {result_1}\n→ {result_2}\n→ {result_3}\n\nWho's ready to make the change?\n\n#{topic} #StopDoing #BetterWay",
                'category' => 'controversial',
                'industry' => 'general',
                'engagement_score' => 90,
                'variables' => json_encode(['common_practice', 'reason_1', 'data_point', 'alternative_1', 'alternative_2', 'alternative_3', 'timeframe', 'result_1', 'result_2', 'result_3', 'topic']),
                'description' => 'Stop doing X - actionable advice'
            ],

            // ===== INDUSTRY-SPECIFIC TEMPLATES =====
            
            // MARKETING
            [
                'title' => 'Marketing Campaign Breakdown',
                'content' => "Campaign breakdown: {campaign_name}\n\nObjective:\n{goal}\n\nStrategy:\n• Channel: {channel}\n• Budget: {budget}\n• Timeline: {timeline}\n\nTactics:\n1. {tactic_1}\n2. {tactic_2}\n3. {tactic_3}\n\nResults:\n📈 {metric_1}: {result_1}\n📈 {metric_2}: {result_2}\n📈 {metric_3}: {result_3}\n\nROI: {roi}\n\nKey learnings:\n{lesson}\n\nWhat would you do differently?\n\n#Marketing #Campaign #Results",
                'category' => 'value_drop',
                'industry' => 'marketing',
                'engagement_score' => 88,
                'variables' => json_encode(['campaign_name', 'goal', 'channel', 'budget', 'timeline', 'tactic_1', 'tactic_2', 'tactic_3', 'metric_1', 'result_1', 'metric_2', 'result_2', 'metric_3', 'result_3', 'roi', 'lesson']),
                'description' => 'Campaign breakdown - transparency'
            ],

            // SALES
            [
                'title' => 'Cold Email That Got 60% Response Rate',
                'content' => "This cold email got a 60% response rate:\n\n━━━━━━━━━━━━━\nSubject: {subject_line}\n\nBody:\n{email_body}\n━━━━━━━━━━━━━\n\nWhy it worked:\n\n1. {reason_1}\n2. {reason_2}\n3. {reason_3}\n\nKey elements:\n✓ {element_1}\n✓ {element_2}\n✓ {element_3}\n\nResults:\n• Sent: {sent_count}\n• Opened: {open_rate}%\n• Replied: {reply_rate}%\n• Meetings booked: {meetings}\n\nSteal this template.\n\n#Sales #ColdEmail #Outreach",
                'category' => 'value_drop',
                'industry' => 'sales',
                'engagement_score' => 91,
                'variables' => json_encode(['subject_line', 'email_body', 'reason_1', 'reason_2', 'reason_3', 'element_1', 'element_2', 'element_3', 'sent_count', 'open_rate', 'reply_rate', 'meetings']),
                'description' => 'Email template - actionable resource'
            ],

            // TECH
            [
                'title' => 'Tech Stack Breakdown',
                'content' => "Our tech stack for {product/project}:\n\nFrontend:\n• {frontend_tech_1}\n• {frontend_tech_2}\n\nBackend:\n• {backend_tech_1}\n• {backend_tech_2}\n\nDatabase:\n• {database}\n\nInfrastructure:\n• {infrastructure}\n\nWhy these choices:\n\n{tech_1}: {reason_1}\n{tech_2}: {reason_2}\n{tech_3}: {reason_3}\n\nChallenges:\n❌ {challenge_1}\n❌ {challenge_2}\n\nWins:\n✅ {win_1}\n✅ {win_2}\n\nWhat's your stack?\n\n#Tech #Development #TechStack",
                'category' => 'value_drop',
                'industry' => 'tech',
                'engagement_score' => 84,
                'variables' => json_encode(['product/project', 'frontend_tech_1', 'frontend_tech_2', 'backend_tech_1', 'backend_tech_2', 'database', 'infrastructure', 'tech_1', 'reason_1', 'tech_2', 'reason_2', 'tech_3', 'reason_3', 'challenge_1', 'challenge_2', 'win_1', 'win_2']),
                'description' => 'Tech stack - developer content'
            ],

            // FINANCE
            [
                'title' => 'How I Save X Per Month',
                'content' => "How I save \${amount} per month:\n\nCategory 1: {category_1}\n💰 Before: \${before_1}\n💰 After: \${after_1}\n💰 Saved: \${saved_1}\nHow: {method_1}\n\nCategory 2: {category_2}\n💰 Before: \${before_2}\n💰 After: \${after_2}\n💰 Saved: \${saved_2}\nHow: {method_2}\n\nCategory 3: {category_3}\n💰 Before: \${before_3}\n💰 After: \${after_3}\n💰 Saved: \${saved_3}\nHow: {method_3}\n\nTotal saved: \${total_saved}/month\nAnnual savings: \${annual_savings}\n\nSmall changes. Big results.\n\nWhat's your best money-saving tip?\n\n#Finance #Savings #Money",
                'category' => 'value_drop',
                'industry' => 'finance',
                'engagement_score' => 87,
                'variables' => json_encode(['amount', 'category_1', 'before_1', 'after_1', 'saved_1', 'method_1', 'category_2', 'before_2', 'after_2', 'saved_2', 'method_2', 'category_3', 'before_3', 'after_3', 'saved_3', 'method_3', 'total_saved', 'annual_savings']),
                'description' => 'Money-saving tips - practical value'
            ],

            // EDUCATION
            [
                'title' => 'How I Learn Anything Fast',
                'content' => "How I learn {skill} in {timeframe}:\n\nStep 1: {step_1_name}\n{step_1_detail}\nTime: {time_1}\n\nStep 2: {step_2_name}\n{step_2_detail}\nTime: {time_2}\n\nStep 3: {step_3_name}\n{step_3_detail}\nTime: {time_3}\n\nResources I used:\n📚 {resource_1}\n📚 {resource_2}\n📚 {resource_3}\n\nCommon mistakes to avoid:\n❌ {mistake_1}\n❌ {mistake_2}\n\nPro tips:\n✓ {tip_1}\n✓ {tip_2}\n\nTotal time invested: {total_time}\nCurrent level: {current_level}\n\nYou can learn anything.\n\nWhat skill are you learning?\n\n#Learning #Skills #Education",
                'category' => 'tip',
                'industry' => 'education',
                'engagement_score' => 85,
                'variables' => json_encode(['skill', 'timeframe', 'step_1_name', 'step_1_detail', 'time_1', 'step_2_name', 'step_2_detail', 'time_2', 'step_3_name', 'step_3_detail', 'time_3', 'resource_1', 'resource_2', 'resource_3', 'mistake_1', 'mistake_2', 'tip_1', 'tip_2', 'total_time', 'current_level']),
                'description' => 'Learning guide - educational'
            ],

            // ===== ADDITIONAL HIGH-ENGAGEMENT FORMATS =====
            [
                'title' => 'The Ultimate Checklist',
                'content' => "The ultimate {topic} checklist:\n\nBefore you start:\n☐ {item_1}\n☐ {item_2}\n☐ {item_3}\n\nDuring:\n☐ {item_4}\n☐ {item_5}\n☐ {item_6}\n\nAfter:\n☐ {item_7}\n☐ {item_8}\n☐ {item_9}\n\nPro tips:\n💡 {pro_tip_1}\n💡 {pro_tip_2}\n\nCommon mistakes:\n⚠️ {mistake_1}\n⚠️ {mistake_2}\n\nBookmark this. You'll need it.\n\n#{topic} #Checklist #Guide",
                'category' => 'tip',
                'industry' => 'general',
                'engagement_score' => 86,
                'variables' => json_encode(['topic', 'item_1', 'item_2', 'item_3', 'item_4', 'item_5', 'item_6', 'item_7', 'item_8', 'item_9', 'pro_tip_1', 'pro_tip_2', 'mistake_1', 'mistake_2']),
                'description' => 'Checklist format - actionable & saveable'
            ],
            [
                'title' => 'The Complete Beginner\'s Guide',
                'content' => "Complete beginner's guide to {topic}:\n\n🎯 What is {topic}?\n{definition}\n\n🎯 Why it matters:\n{importance}\n\n🎯 How to get started:\n\nWeek 1: {week_1_focus}\n{week_1_detail}\n\nWeek 2: {week_2_focus}\n{week_2_detail}\n\nWeek 3: {week_3_focus}\n{week_3_detail}\n\nWeek 4: {week_4_focus}\n{week_4_detail}\n\n🎯 Common beginner mistakes:\n1. {beginner_mistake_1}\n2. {beginner_mistake_2}\n\n🎯 Next steps:\n{next_steps}\n\nSave this post.\n\nShare with someone starting their journey.\n\n#{topic} #Beginner #Guide",
                'category' => 'tip',
                'industry' => 'education',
                'engagement_score' => 88,
                'variables' => json_encode(['topic', 'definition', 'importance', 'week_1_focus', 'week_1_detail', 'week_2_focus', 'week_2_detail', 'week_3_focus', 'week_3_detail', 'week_4_focus', 'week_4_detail', 'beginner_mistake_1', 'beginner_mistake_2', 'next_steps']),
                'description' => 'Beginner guide - comprehensive & helpful'
            ],
            [
                'title' => 'The X-Day Challenge',
                'content' => "I challenged myself to {goal} in {days} days.\n\nHere's what happened:\n\nDay 1-{range_1}:\n{period_1_summary}\n\nDay {range_2}:\n{period_2_summary}\n\nDay {range_3}:\n{period_3_summary}\n\nResults:\n✅ {result_1}\n✅ {result_2}\n✅ {result_3}\n\nBiggest surprise:\n{surprise}\n\nBiggest challenge:\n{challenge}\n\nWould I do it again?\n{verdict}\n\nWant to try this challenge?\n\nHere's how to start:\n{how_to_start}\n\n#{topic} #Challenge #Experiment",
                'category' => 'story',
                'industry' => 'general',
                'engagement_score' => 87,
                'variables' => json_encode(['goal', 'days', 'range_1', 'period_1_summary', 'range_2', 'period_2_summary', 'range_3', 'period_3_summary', 'result_1', 'result_2', 'result_3', 'surprise', 'challenge', 'verdict', 'how_to_start', 'topic']),
                'description' => 'Challenge post - engaging story format'
            ],
            [
                'title' => 'Things Nobody Tells You About',
                'content' => "Things nobody tells you about {topic}:\n\n1️⃣ {truth_1}\n\nThey say: {common_belief_1}\nReality: {actual_reality_1}\n\n2️⃣ {truth_2}\n\nThey say: {common_belief_2}\nReality: {actual_reality_2}\n\n3️⃣ {truth_3}\n\nThey say: {common_belief_3}\nReality: {actual_reality_3}\n\n4️⃣ {truth_4}\n\nThey say: {common_belief_4}\nReality: {actual_reality_4}\n\nThe biggest secret?\n\n{biggest_secret}\n\nNow you know.\n\nWhat surprised you most?\n\n#{topic} #Truth #RealTalk",
                'category' => 'controversial',
                'industry' => 'general',
                'engagement_score' => 89,
                'variables' => json_encode(['topic', 'truth_1', 'common_belief_1', 'actual_reality_1', 'truth_2', 'common_belief_2', 'actual_reality_2', 'truth_3', 'common_belief_3', 'actual_reality_3', 'truth_4', 'common_belief_4', 'actual_reality_4', 'biggest_secret']),
                'description' => 'Hidden truths - eye-opening content'
            ],
            [
                'title' => 'The Psychology Behind',
                'content' => "The psychology behind {topic}:\n\n🧠 Principle 1: {principle_1}\n\nWhat it means:\n{explanation_1}\n\nHow to use it:\n{application_1}\n\n🧠 Principle 2: {principle_2}\n\nWhat it means:\n{explanation_2}\n\nHow to use it:\n{application_2}\n\n🧠 Principle 3: {principle_3}\n\nWhat it means:\n{explanation_3}\n\nHow to use it:\n{application_3}\n\nReal example:\n{real_world_example}\n\nThis changed how I think about {topic}.\n\nFascinating, right?\n\n#Psychology #{topic} #Mindset",
                'category' => 'value_drop',
                'industry' => 'general',
                'engagement_score' => 85,
                'variables' => json_encode(['topic', 'principle_1', 'explanation_1', 'application_1', 'principle_2', 'explanation_2', 'application_2', 'principle_3', 'explanation_3', 'application_3', 'real_world_example']),
                'description' => 'Psychology insights - intellectually engaging'
            ],
            [
                'title' => 'My Monthly Revenue Breakdown',
                'content' => "My monthly revenue breakdown:\n\n💰 Total: \${total_revenue}\n\nRevenue sources:\n\n1. {source_1}: \${revenue_1} ({percentage_1}%)\n{detail_1}\n\n2. {source_2}: \${revenue_2} ({percentage_2}%)\n{detail_2}\n\n3. {source_3}: \${revenue_3} ({percentage_3}%)\n{detail_3}\n\nExpenses:\n• {expense_1}: \${expense_amount_1}\n• {expense_2}: \${expense_amount_2}\n• {expense_3}: \${expense_amount_3}\n\nNet profit: \${net_profit}\n\nYear-over-year growth: {yoy_growth}%\n\nWhat I learned:\n{lesson}\n\nTransparency builds trust.\n\nQuestions?\n\n#Revenue #Transparency #Business",
                'category' => 'behind_scenes',
                'industry' => 'entrepreneurship',
                'engagement_score' => 91,
                'variables' => json_encode(['total_revenue', 'source_1', 'revenue_1', 'percentage_1', 'detail_1', 'source_2', 'revenue_2', 'percentage_2', 'detail_2', 'source_3', 'revenue_3', 'percentage_3', 'detail_3', 'expense_1', 'expense_amount_1', 'expense_2', 'expense_amount_2', 'expense_3', 'expense_amount_3', 'net_profit', 'yoy_growth', 'lesson']),
                'description' => 'Revenue transparency - builds trust'
            ],
            [
                'title' => 'The Ultimate Resource List',
                'content' => "The ultimate {topic} resource list:\n\n📚 Books:\n• {book_1} by {author_1}\n• {book_2} by {author_2}\n• {book_3} by {author_3}\n\n🎙 Podcasts:\n• {podcast_1}\n• {podcast_2}\n• {podcast_3}\n\n👥 People to follow:\n• {person_1}\n• {person_2}\n• {person_3}\n\n🔧 Tools:\n• {tool_1}\n• {tool_2}\n• {tool_3}\n\n🎓 Courses:\n• {course_1}\n• {course_2}\n\n💎 Hidden gems:\n• {gem_1}\n• {gem_2}\n\nBookmark this.\n\nYou'll thank me later.\n\n#{topic} #Resources #Learning",
                'category' => 'listicle',
                'industry' => 'general',
                'engagement_score' => 92,
                'variables' => json_encode(['topic', 'book_1', 'author_1', 'book_2', 'author_2', 'book_3', 'author_3', 'podcast_1', 'podcast_2', 'podcast_3', 'person_1', 'person_2', 'person_3', 'tool_1', 'tool_2', 'tool_3', 'course_1', 'course_2', 'gem_1', 'gem_2']),
                'description' => 'Ultimate resource list - highly saveable'
            ],
            [
                'title' => 'Red Flags to Watch For',
                'content' => "🚩 Red flags in {context}:\n\n1. {red_flag_1}\n\nWhy it's bad:\n{explanation_1}\n\nWhat to do:\n{action_1}\n\n2. {red_flag_2}\n\nWhy it's bad:\n{explanation_2}\n\nWhat to do:\n{action_2}\n\n3. {red_flag_3}\n\nWhy it's bad:\n{explanation_3}\n\nWhat to do:\n{action_3}\n\n4. {red_flag_4}\n\nWhy it's bad:\n{explanation_4}\n\nWhat to do:\n{action_4}\n\nGreen flags to look for instead:\n✅ {green_flag_1}\n✅ {green_flag_2}\n✅ {green_flag_3}\n\nTrust your gut.\n\nWhat other red flags have you seen?\n\n#RedFlags #{context} #Warning",
                'category' => 'value_drop',
                'industry' => 'general',
                'engagement_score' => 88,
                'variables' => json_encode(['context', 'red_flag_1', 'explanation_1', 'action_1', 'red_flag_2', 'explanation_2', 'action_2', 'red_flag_3', 'explanation_3', 'action_3', 'red_flag_4', 'explanation_4', 'action_4', 'green_flag_1', 'green_flag_2', 'green_flag_3']),
                'description' => 'Red flags - protective advice'
            ],
            [
                'title' => 'My Morning Routine for Success',
                'content' => "My morning routine for {outcome}:\n\n5:00 AM - {activity_1}\nWhy: {benefit_1}\n\n5:30 AM - {activity_2}\nWhy: {benefit_2}\n\n6:00 AM - {activity_3}\nWhy: {benefit_3}\n\n6:30 AM - {activity_4}\nWhy: {benefit_4}\n\n7:00 AM - {activity_5}\nWhy: {benefit_5}\n\nBy 7:30 AM, I've already:\n✅ {achievement_1}\n✅ {achievement_2}\n✅ {achievement_3}\n\nMost people are just waking up.\n\nI've won the day.\n\nWhat does your morning routine look like?\n\n#MorningRoutine #Productivity #Success",
                'category' => 'behind_scenes',
                'industry' => 'productivity',
                'engagement_score' => 83,
                'variables' => json_encode(['outcome', 'activity_1', 'benefit_1', 'activity_2', 'benefit_2', 'activity_3', 'benefit_3', 'activity_4', 'benefit_4', 'activity_5', 'benefit_5', 'achievement_1', 'achievement_2', 'achievement_3']),
                'description' => 'Morning routine - aspirational'
            ],
            [
                'title' => 'The Harsh Truth About',
                'content' => "The harsh truth about {topic} that nobody wants to hear:\n\n{harsh_truth}\n\nI know it's not what you want to hear.\n\nBut it's what you need to hear.\n\nHere's why:\n\n→ {reason_1}\n→ {reason_2}\n→ {reason_3}\n\nI learned this the hard way:\n{personal_story}\n\nWhat you should do instead:\n\n1. {alternative_1}\n2. {alternative_2}\n3. {alternative_3}\n\nFacing reality is the first step to progress.\n\nReady for the truth?\n\n#{topic} #HarshTruth #RealTalk",
                'category' => 'controversial',
                'industry' => 'general',
                'engagement_score' => 90,
                'variables' => json_encode(['topic', 'harsh_truth', 'reason_1', 'reason_2', 'reason_3', 'personal_story', 'alternative_1', 'alternative_2', 'alternative_3']),
                'description' => 'Harsh truth - bold & honest'
            ],
            [
                'title' => 'The Compound Effect of',
                'content' => "The compound effect of {small_action}:\n\nDay 1:\n{day_1_result}\n\nDay 30:\n{day_30_result}\n\nDay 90:\n{day_90_result}\n\nDay 365:\n{day_365_result}\n\nThe math:\n\nStarting point: {starting_point}\nDaily improvement: {daily_improvement}%\nCompounded result: {compound_result}\n\nSmall consistent actions lead to massive results.\n\nI started with {your_starting_point}.\n\nNow: {your_current_state}\n\nStart today. Future you will thank you.\n\nWhat small action will you commit to?\n\n#CompoundEffect #Consistency #{topic}",
                'category' => 'value_drop',
                'industry' => 'general',
                'engagement_score' => 86,
                'variables' => json_encode(['small_action', 'day_1_result', 'day_30_result', 'day_90_result', 'day_365_result', 'starting_point', 'daily_improvement', 'compound_result', 'your_starting_point', 'your_current_state', 'topic']),
                'description' => 'Compound effect - motivational math'
            ],
        ];

        // Insert all templates
        foreach ($templates as $template) {
            PostTemplate::create($template);
        }

        $this->command->info('✅ Created ' . count($templates) . ' post templates successfully!');
    }
}

