<?php
// Course content for the demo/seed data. Each week = lessons + a quiz (+ optional assignment).
// Quiz question format: [question, [options...], index of correct option (0-based), explanation shown if wrong]

return [

// =====================================================================================
[
 'title' => 'AI Literacy for Everyday Life', 'category' => 'AI & Digital Literacy', 'level' => 'Beginner', 'hours' => 3, 'color' => '#0b5cd5',
 'owner' => 'grace@youthconnect.test',
 'summary' => 'Understand what AI is, use AI tools with confidence, and know how to check, protect and question what they produce.',
 'description' => "Artificial intelligence is already part of daily life, from mobile money fraud alerts to translation and search. This course explains AI in plain language, with no coding required.\n\nOver three weeks you will learn how AI systems learn from data, how to write clear requests for AI assistants, how to check their answers, and how to protect your privacy. You will finish with a personal plan for using AI responsibly in your studies or work.",
 'learn' => "Explain in simple words what AI is and how it learns from data\nWrite clear, specific requests (prompts) to get useful AI answers\nCheck AI output for mistakes and made-up facts\nProtect personal information when using AI tools\nDiscuss fairness, bias and how AI is changing jobs",
 'skills' => 'AI literacy, Prompt writing, Fact checking, Digital safety, Ethical thinking',
 'weeks' => [
  ['title' => 'What is AI, really?', 'desc' => 'See where AI already shows up in your life and how machines learn patterns from examples.', 'hours' => 3,
   'lessons' => [
    ['reading', 'AI in your pocket', 10, "Many phone apps already use AI. Your bank or mobile money service may flag unusual transactions. Your keyboard predicts the next word. Maps suggest the fastest route. Translation apps turn Swahili into English in seconds.\n\nAI is software that finds patterns in large amounts of examples, then uses those patterns to make predictions or suggestions. It does not think like a person. It is very good at narrow tasks and can fail in surprising ways.", true],
    ['reading', 'How machines learn from data', 12, "Traditional software follows rules that a programmer writes. A machine learning system is different: it is shown many examples and works out the rules itself.\n\n## Training data\nIf you show a system thousands of photos labelled \"maize\" or \"cassava\", it can learn to tell them apart. The examples are called training data.\n\n## Garbage in, garbage out\nBad, missing or one-sided data produces bad results. If the examples mostly come from one region or group, the system may work poorly for everyone else.", false],
    ['discussion', 'Spot the AI', 5, "Think of one app or service you used this week that probably uses AI. What does it predict or suggest? Share it in the course discussion and read two other answers.", false],
   ],
   'quiz' => [
    ['Which statement best describes artificial intelligence?', ['Software that finds patterns in examples and uses them to predict or suggest', 'A robot that thinks exactly like a human', 'Any program that uses the internet', 'A type of computer screen'], 0, 'AI systems learn patterns from data. They do not think like people.'],
    ['What is "training data"?', ['The examples an AI system learns from', 'The password used to log in', 'A lesson for human staff', 'The speed of your internet'], 0, 'Training data is the set of examples used to teach the system.'],
    ['An AI crop-disease app was trained only on photos from one region. What risk does this create?', ['It may work poorly on crops from other regions', 'It will always work perfectly everywhere', 'It will stop using electricity', 'It will translate to Swahili'], 0, 'One-sided training data can make results unreliable for people or places not represented.'],
    ['Which of these is the best example of everyday AI?', ['Spoken words turned into text on your phone', 'A paper calendar', 'A battery charger', 'A printed map'], 0, 'Speech-to-text learns patterns in how language sounds.'],
   ], 'assignment' => null],
  ['title' => 'Using AI tools well', 'desc' => 'Write clear prompts and learn why every AI answer needs to be checked.', 'hours' => 3,
   'lessons' => [
    ['reading', 'Asking good questions (prompts)', 12, "The quality of an AI answer depends on the quality of your request. A good prompt is specific.\n\n- Say who you are and what you need: \"I am a first-year student in Kampala.\"\n- Give the task and the format: \"Explain budgeting in five short bullet points.\"\n- Add limits: audience, length, language.\n- Improve step by step: ask for a shorter version, a simpler example, or a different tone.\n\nVague request: \"Tell me about farming.\" Better request: \"List four low-cost ways a smallholder maize farmer can reduce post-harvest losses.\"", false],
    ['reading', 'Checking what AI tells you', 12, "AI assistants can sound confident while being wrong. They sometimes invent facts, names, numbers and web links. This is often called a hallucination.\n\n## Check before you trust\n- Compare with a trusted source such as a government site, a university or a respected news organisation.\n- Double-check every number, date and quotation.\n- Ask the tool to explain its reasoning, then test that reasoning yourself.\n\nYou are responsible for what you submit or share, even if an AI helped you write it.", false],
    ['discussion', 'Improve a prompt', 8, "Write a vague request for an AI assistant, then rewrite it to be specific using the tips above. Post both versions in the discussion and say what you changed.", false],
   ],
   'quiz' => [
    ['Which prompt is most likely to give a useful answer?', ['"Give me three tips for a first job interview in Nairobi, in simple English."', '"Interview."', '"Help."', '"Tell me everything."'], 0, 'Specific context, task and format make answers more useful.'],
    ['What is a "hallucination" in AI?', ['A confident answer that contains made-up or wrong information', 'A slow internet connection', 'A picture that is too bright', 'A password reset email'], 0, 'AI can invent facts that sound believable.'],
    ['An AI tool gives you a statistic for your report. What should you do?', ['Verify it with a trusted source before using it', 'Use it because the tool sounded sure', 'Delete your report', 'Share it immediately'], 0, 'Always verify numbers and facts yourself.'],
    ['If the first AI answer is too long, what is a good next step?', ['Ask for a shorter version or a simpler example', 'Give up on AI forever', 'Copy it without reading', 'Switch off the phone'], 0, 'You can improve results by refining your request step by step.'],
   ], 'assignment' => null],
  ['title' => 'AI, ethics and your future', 'desc' => 'Protect your privacy, understand bias, and plan how to use AI responsibly.', 'hours' => 3,
   'lessons' => [
    ['reading', 'Privacy and safety', 12, "Anything you type into an AI tool may be stored or used to improve the service. Treat it like a public notice board.\n\n## Never share\n- Passwords and PINs, including mobile money PINs\n- National ID, passport or bank numbers\n- Private photos or messages of other people\n- Confidential information from your school or employer\n\nIf you are unsure, remove names and details, or do not use the tool for that task.", false],
    ['reading', 'Bias, fairness and the future of work', 14, "AI learns from data created by people, so it can copy human unfairness. A hiring tool trained mostly on one group's past decisions may unfairly rank others lower.\n\n## Work is changing\nAI is likely to change many tasks rather than remove every job. People who combine their own knowledge with AI tools, check results carefully, communicate well and keep learning will stay valuable.", false],
    ['discussion', 'Where should humans decide?', 8, "Name one decision, for example approving a loan or marking an exam, where you believe a person should always make the final call, even if AI helps. Explain why.", false],
   ],
   'quiz' => [
    ['Which of these should you NEVER type into an AI chatbot?', ['Your mobile money PIN', 'A question about healthy meals', 'A request to explain photosynthesis', 'A request to summarise a public article'], 0, 'PINs and passwords must stay private.'],
    ['Why can AI systems be biased?', ['They learn from data made by people, which can contain unfairness', 'Because they are too fast', 'Because they use too little electricity', 'Because they cannot read'], 0, 'Biased data leads to biased results.'],
    ['Which approach best prepares you for a workplace with more AI?', ['Combine your knowledge with AI tools, check results, and keep learning', 'Refuse to learn anything new', 'Trust every AI answer', 'Only copy and paste'], 0, 'Judgement, communication and learning remain valuable.'],
    ['Before sharing a classmate\'s photo with an AI app, you should:', ['Ask for their permission and consider whether it is necessary', 'Upload it without telling them', 'Post it publicly first', 'Ignore privacy'], 0, 'Other people\'s personal information needs their consent.'],
   ],
   'assignment' => ['My personal AI plan', "Choose one real task from your studies, work or community project (for example writing a report, planning a budget or preparing a presentation).\n\nIn 150 to 250 words describe: (1) how you would use an AI tool for part of the task, with one example prompt; (2) how you would check the result; and (3) what personal information you would never put into the tool."]],
 ],
],

// =====================================================================================
[
 'title' => 'Digital Skills for the Workplace', 'category' => 'AI & Digital Literacy', 'level' => 'Beginner', 'hours' => 3, 'color' => '#0e7c86',
 'owner' => 'grace@youthconnect.test',
 'summary' => 'Work safely and productively online: secure accounts, professional email, files, spreadsheets and cloud tools.',
 'description' => "Employers expect new staff to work safely and confidently with digital tools. This beginner course covers the essentials: protecting your accounts, writing professional messages, organising files and making simple spreadsheets.\n\nEvery lesson works on a phone, and examples use situations common in East African workplaces and projects.",
 'learn' => "Create strong passwords and use two-step verification\nRecognise phishing and scam messages\nWrite clear, polite professional emails\nOrganise, name and back up files\nBuild a simple spreadsheet with formulas and a chart",
 'skills' => 'Online safety, Email etiquette, File management, Spreadsheets, Cloud tools',
 'weeks' => [
  ['title' => 'Working online safely', 'desc' => 'Protect your accounts and spot scams before they cost you.', 'hours' => 3,
   'lessons' => [
    ['reading', 'Passwords and two-step verification', 10, "A strong password is long, unique and hard to guess. A passphrase of several unrelated words, such as \"river-mango-clock-seven\", is easier to remember than random symbols and is stronger than a short complex password.\n\nUse a different password for every important account. Turn on two-step verification (a code sent to your phone or an app) for email, social media and money services. Even if someone learns your password, they still cannot get in.", true],
    ['reading', 'Spotting phishing and scams', 12, "Phishing messages try to trick you into sharing passwords, PINs or money.\n\n## Warning signs\n- Urgent threats: \"Your account will close today!\"\n- Requests for a PIN, password or one-time code\n- Unknown links or a sender address that looks slightly wrong\n- Offers that are too good to be true, such as a prize you never entered for\n\nA real bank or mobile money company will never ask for your PIN. When in doubt, do not click. Contact the company using the number you already know.", false],
    ['discussion', 'Share a scam story', 6, "Describe a scam message you or someone you know received (remove real names and numbers). What was the warning sign?", false],
   ],
   'quiz' => [
    ['Which password is strongest?', ['river-mango-clock-seven', 'password123', 'Kampala1', 'qwerty'], 0, 'Long, unique passphrases are strong and easier to remember.'],
    ['What does two-step verification add?', ['A second check, like a code, besides your password', 'A faster internet connection', 'A bigger screen', 'More storage'], 0, 'A second step protects the account even if the password leaks.'],
    ['A message says "Send your mobile money PIN to claim your prize." This is:', ['A scam', 'A normal service message', 'A bank policy', 'A software update'], 0, 'Nobody legitimate asks for your PIN.'],
    ['What is the safest response to a suspicious link?', ['Do not click; contact the organisation using a number you already trust', 'Click quickly before it expires', 'Forward it to all friends', 'Reply with your details'], 0, 'Verify through a separate trusted channel.'],
   ], 'assignment' => null],
  ['title' => 'Everyday productivity tools', 'desc' => 'Communicate professionally by email and work with documents in the cloud.', 'hours' => 3,
   'lessons' => [
    ['reading', 'Writing a professional email', 12, "A good work email is short, clear and polite.\n\n- Subject line: say what it is about, for example \"Request for meeting on Thursday\".\n- Greeting: \"Dear Ms Achieng,\" or \"Hello Peter,\".\n- Body: one main point per paragraph. State what you need and by when.\n- Close politely: \"Thank you\" and your full name.\n\nRead it once before sending. Check the recipient, the attachment and the tone.", false],
    ['reading', 'Documents in the cloud', 12, "Cloud tools let several people edit the same document and keep earlier versions. They also protect work if your phone or laptop is lost.\n\n## Good habits\n- Save important files online as well as on your device.\n- Share with specific people, not \"anyone with the link\", when the content is private.\n- Use comments and suggestions instead of sending many copies by email.\n- Name versions clearly, for example \"Budget_2026-03_v2\".", false],
    ['discussion', 'Rewrite the email', 8, "Rewrite this weak message as a professional email: \"hi send me the report now\". Post your improved version.", false],
   ],
   'quiz' => [
    ['What makes a good email subject line?', ['It states clearly what the email is about', 'It is left empty', 'It is written in capital letters', 'It says only "Hi"'], 0, 'A clear subject helps the reader act quickly.'],
    ['What is a benefit of editing documents in the cloud?', ['Several people can work on one copy and keep earlier versions', 'It removes the need for passwords', 'It makes files disappear', 'It works only with no internet'], 0, 'Cloud tools support collaboration and version history.'],
    ['Which closing is most appropriate for a work email?', ['"Thank you, Ruth Namutebi"', '"bye"', '"???"', '"Whatever"'], 0, 'A polite close with your name is professional.'],
    ['When a document contains private information you should share it with:', ['Only the specific people who need it', 'Anyone with the link', 'The public', 'Nobody, ever, even teammates'], 0, 'Limit access to people who need it.'],
   ], 'assignment' => null],
  ['title' => 'Working with files and data', 'desc' => 'Organise your files and build a simple spreadsheet with formulas and a chart.', 'hours' => 3,
   'lessons' => [
    ['reading', 'Organising and backing up files', 10, "Messy files waste time and risk loss. Create a few clear folders (for example Projects, Studies, Personal) and name files with a date and a short description.\n\nFollow the 3-2-1 idea in a simple form: keep your important work in at least two places, such as your device and an online account, and remember to update the copy regularly.", false],
    ['reading', 'Spreadsheet basics', 15, "A spreadsheet stores data in rows and columns. Formulas do the arithmetic for you.\n\n- =SUM(B2:B10) adds the numbers in cells B2 to B10.\n- =AVERAGE(B2:B10) gives the average.\n- =B2*C2 multiplies two cells, for example price times quantity.\n\nA chart makes numbers easier to understand. Use a column chart to compare amounts and a line chart to show change over time. Always label the chart and the axes.", false],
    ['discussion', 'What would you track?', 6, "Name one thing in your life or project you could track in a spreadsheet, such as savings, crop yields or attendance. What columns would you use?", false],
   ],
   'quiz' => [
    ['Which formula adds the numbers in cells B2 to B10?', ['=SUM(B2:B10)', '=ADD B2 B10', '=B2+B10 only', '=PLUS(B)'], 0, 'SUM adds a range of cells.'],
    ['A chart is best used to:', ['Make numbers easier to understand at a glance', 'Hide the data', 'Replace all text', 'Slow down the computer'], 0, 'Charts summarise patterns visually.'],
    ['Why keep important files in two places?', ['If one is lost or damaged you still have a copy', 'To use more storage', 'It is required by law', 'It makes the files bigger'], 0, 'Backups protect your work.'],
    ['Which file name is clearest?', ['Budget_2026-03_v2', 'final', 'document1', 'new new new'], 0, 'Descriptive names with dates and versions are easy to find.'],
   ],
   'assignment' => ['Plan a project budget sheet', "Imagine you are managing a small project with a budget, for example a school garden or a youth workshop.\n\nIn 120 to 200 words describe the spreadsheet you would build: the column headings you would use, three formulas you would include (and what each does), and one chart that would help your team understand the budget."]],
 ],
],

// =====================================================================================
[
 'title' => 'Critical Thinking and Spotting Misinformation', 'category' => 'Critical Thinking', 'level' => 'Beginner', 'hours' => 3, 'color' => '#7a3fc4',
 'owner' => 'david@youthconnect.test',
 'summary' => 'Question what you read, verify claims quickly, and build clear, evidence-based arguments.',
 'description' => "False and misleading information spreads fast on messaging apps and social media, and it can harm health, money and trust in communities. Critical thinking is the habit of asking: how do I know this is true?\n\nThis course teaches practical, quick checks you can do on your phone, and the reasoning skills to explain your conclusions to others.",
 'learn' => "Recognise common thinking biases in yourself and others\nTell facts, opinions and claims apart\nUse the SIFT method to check a source in minutes\nSpot common reasoning mistakes\nBuild a short argument supported by evidence",
 'skills' => 'Critical thinking, Fact checking, Media literacy, Argumentation, Decision making',
 'weeks' => [
  ['title' => 'How we think', 'desc' => 'Understand the mental shortcuts that make all of us easy to fool.', 'hours' => 3,
   'lessons' => [
    ['reading', 'Facts, opinions and claims', 10, "A fact can be checked and shown to be true or false: \"The Nile flows through Uganda.\" An opinion is a personal view: \"The Nile is the most beautiful river.\" A claim is a statement that might be true and needs evidence: \"This herb cures malaria.\"\n\nCritical thinkers ask of every claim: who says so, what is the evidence, and what might they gain?", true],
    ['reading', 'Mental shortcuts and bias', 12, "Our brains use shortcuts to decide quickly. They are useful but can mislead us.\n\n## Confirmation bias\nWe notice and remember information that agrees with what we already believe, and ignore what does not.\n\n## Emotional reasoning\nStrong feelings such as fear or anger make us share messages before checking them.\n\nSlow down when a message makes you feel very strong emotion. That is exactly when to check first.", false],
    ['discussion', 'A time I was wrong', 8, "Describe a time you believed something that turned out to be false. What made it feel believable, and how did you find out?", false],
   ],
   'quiz' => [
    ['Which of these is an opinion?', ['"Matooke is the tastiest food."', '"Kampala is in Uganda."', '"Water boils at 100 degrees Celsius at sea level."', '"Kenya has 47 counties."'], 0, 'Taste is a personal view, not a checkable fact.'],
    ['What is confirmation bias?', ['Noticing information that agrees with what you already believe', 'Checking many sources', 'Changing your mind quickly', 'Memorising numbers'], 0, 'We tend to favour evidence that supports our existing beliefs.'],
    ['A message makes you very angry and says "Share now!" What should you do first?', ['Pause and check the claim before sharing', 'Share it to warn others', 'Reply with insults', 'Delete your phone'], 0, 'Strong emotion is a signal to slow down and verify.'],
    ['A claim needs:', ['Evidence', 'Many shares', 'A loud voice', 'A nice picture'], 0, 'Evidence, not popularity, supports a claim.'],
   ], 'assignment' => null],
  ['title' => 'Checking claims quickly', 'desc' => 'Use the SIFT method and simple tools to verify what you see online.', 'hours' => 3,
   'lessons' => [
    ['reading', 'The SIFT method', 14, "SIFT gives four quick moves for checking information.\n\n- Stop: pause before you react or share.\n- Investigate the source: who is behind this? Search their name and see what trusted sources say about them.\n- Find better coverage: look for the same claim in reliable news or official sites.\n- Trace claims to the original: find the study, photo or quotation the message refers to.\n\nOften two minutes is enough to learn that a viral message is false or missing context.", false],
    ['reading', 'Pictures, dates and context', 12, "Old photos and videos are often shared as if they were new. Before trusting an image, run a reverse image search to find where it first appeared. Check the date and place.\n\nAlso look for context. A real quotation can be shortened to change its meaning, and a real chart can have a misleading scale. Ask: what is missing?", false],
    ['discussion', 'Check one claim', 10, "Pick a claim you have seen in a group chat or on social media. Use SIFT to check it, then report what you found and how long it took.", false],
   ],
   'quiz' => [
    ['What does the "S" in SIFT remind you to do?', ['Stop and pause before reacting or sharing', 'Send it to everyone', 'Save it for later', 'Subscribe'], 0, 'The first step is to stop.'],
    ['You see a dramatic photo said to be from yesterday. What is a good check?', ['A reverse image search to find where it first appeared', 'Trust it because it has many likes', 'Check only the caption', 'Ignore the date'], 0, 'Reverse image search often reveals old or reused images.'],
    ['"Find better coverage" means:', ['Look for the claim in reliable news or official sources', 'Find more people who agree', 'Buy a newspaper', 'Zoom in on the picture'], 0, 'Reliable corroboration is stronger than repetition.'],
    ['A short clip of a speech seems to say something shocking. What should you consider?', ['Whether the full speech gives a different meaning', 'Only the shocking clip', 'The number of views', 'The video format'], 0, 'Missing context can change the meaning.'],
   ], 'assignment' => null],
  ['title' => 'Reasoning and arguing well', 'desc' => 'Avoid common reasoning mistakes and build an argument that others can trust.', 'hours' => 3,
   'lessons' => [
    ['reading', 'Common reasoning mistakes', 12, "Some arguments look strong but are not.\n\n- Attacking the person: rejecting an idea because of who said it, not what was said.\n- False choice: \"Either we ban phones in class or learning fails.\" Usually there are more options.\n- Hasty generalisation: concluding about everyone from one or two cases.\n- Correlation is not causation: two things happening together does not prove one caused the other.", false],
    ['reading', 'Building a clear argument', 14, "A good argument has three parts: a claim, reasons, and evidence.\n\n1. State your claim in one sentence.\n2. Give two or three reasons.\n3. Support each reason with evidence such as data, an example, or a trusted source.\n4. Acknowledge the strongest opposing point and respond to it.\n\nKeep your tone respectful. The goal is to find what is true, not to win.", false],
    ['discussion', 'Spot the mistake', 8, "Write a short example of one reasoning mistake from this week and explain why it is weak. Read a classmate's example and say which mistake it shows.", false],
   ],
   'quiz' => [
    ['"Everyone from that village is dishonest because one person cheated me." This is:', ['A hasty generalisation', 'A strong argument', 'A proven fact', 'A question'], 0, 'One case does not describe everyone.'],
    ['Ice cream sales and sunburn both rise in summer. Which conclusion is sound?', ['They may share a common cause, hot sunny weather', 'Ice cream causes sunburn', 'Sunburn causes ice cream sales', 'They are unrelated in every way'], 0, 'Correlation does not prove causation.'],
    ['A good argument includes:', ['A claim, reasons and evidence', 'Only strong opinions', 'Only a loud tone', 'Only one example without sources'], 0, 'Reasons must be supported by evidence.'],
    ['Responding to the strongest opposing point makes your argument:', ['More trustworthy', 'Weaker in every case', 'Shorter', 'Less polite'], 0, 'Fairly answering objections builds credibility.'],
   ],
   'assignment' => ['Analyse a viral message', "Choose a message, post or forwarded story you have seen that you suspect may be false or misleading. Remove any personal names or numbers.\n\nIn 150 to 250 words: summarise the claim, show how you used at least two SIFT steps, say what you found, and explain how you would respond to the person who shared it."]],
 ],
],

// =====================================================================================
[
 'title' => 'Intercultural Communication across East Africa', 'category' => 'Intercultural Communication', 'level' => 'Beginner', 'hours' => 3, 'color' => '#c2571a',
 'owner' => 'david@youthconnect.test',
 'summary' => 'Communicate respectfully and clearly with people from different countries, languages and working styles.',
 'description' => "East Africa brings together hundreds of languages and many ways of greeting, deciding and working. These differences are a strength, but they can also cause misunderstanding when people work together online.\n\nThis course helps you understand your own cultural habits, communicate clearly across languages, and run respectful virtual meetings across countries and time zones.",
 'learn' => "Describe how culture shapes communication and expectations\nAvoid stereotypes and assumptions\nUse plain language so everyone can understand\nPlan meetings across time zones and connection limits\nRepair a misunderstanding respectfully",
 'skills' => 'Intercultural communication, Active listening, Plain language, Virtual meetings, Conflict resolution',
 'weeks' => [
  ['title' => 'Culture and communication', 'desc' => 'Understand how culture shapes what we say, how we say it and what we expect.', 'hours' => 3,
   'lessons' => [
    ['reading', 'What is culture?', 10, "Culture is the shared habits, values and ways of doing things in a group. It includes language, greetings, humour, respect for elders, and attitudes to time and decisions.\n\nCulture is like an iceberg. A little is visible, such as food and clothing. Most is hidden, such as beliefs about respect, family and fairness. Misunderstandings usually come from the hidden part.", true],
    ['reading', 'Direct and indirect styles', 12, "In some groups people say exactly what they mean. In others, meaning is carried by tone, context and what is left unsaid, and saying \"no\" directly can seem rude.\n\nNeither style is better. When you work across cultures, do not assume that silence means agreement, or that a polite \"yes\" means the plan is confirmed. Ask open questions such as \"What worries you about this plan?\"", false],
    ['discussion', 'A greeting from home', 6, "Describe how people usually greet each other where you live. What would a visitor need to know to greet someone respectfully?", false],
   ],
   'quiz' => [
    ['Which part of culture is hardest to see?', ['Values and beliefs', 'Clothing', 'Food', 'Music'], 0, 'The hidden part of culture includes values and beliefs.'],
    ['A teammate stays silent when you propose a plan. The best response is to:', ['Ask an open question to learn their view', 'Assume they agree', 'Assume they are rude', 'Stop the project'], 0, 'Silence can mean many things.'],
    ['Which statement about communication styles is best?', ['No style is better; they are different', 'Direct style is always correct', 'Indirect style is always polite', 'Style does not matter'], 0, 'Respect different styles and check understanding.'],
    ['A stereotype is:', ['A fixed idea about all people in a group', 'A proven fact', 'A greeting', 'A language'], 0, 'Stereotypes ignore individual differences.'],
   ], 'assignment' => null],
  ['title' => 'Communicating across languages', 'desc' => 'Use plain language and listening skills so that everyone understands.', 'hours' => 3,
   'lessons' => [
    ['reading', 'Plain language', 12, "Many East Africans speak two or three languages, and English may be a second or third language for some of your teammates. Plain language helps everyone.\n\n- Use short sentences and common words.\n- Avoid idioms such as \"hit the ground running\" and slang.\n- Explain abbreviations the first time.\n- Write one request per message and say clearly what you need and by when.", false],
    ['reading', 'Listening and checking understanding', 12, "Active listening means giving full attention and showing you understand.\n\n- Repeat back in your own words: \"So you will send the draft on Friday. Is that right?\"\n- Ask, do not assume: \"Can you tell me more about that?\"\n- Summarise decisions in writing after a call.\n\nSwahili is widely used across the region and learning a few greetings in your teammates' languages shows respect.", false],
    ['discussion', 'Rewrite in plain language', 8, "Take a sentence full of idioms, such as \"Let's touch base offline and circle back,\" and rewrite it in plain language. Post both versions.", false],
   ],
   'quiz' => [
    ['Which sentence is plainer?', ['"Please send the report by Friday."', '"Kindly revert with deliverables ASAP, going forward."', '"Let us circle back."', '"We should leverage synergies."'], 0, 'Short, direct sentences are easier to understand.'],
    ['Why summarise decisions in writing after a call?', ['It confirms everyone understood the same thing', 'It wastes time', 'It replaces the call', 'It is required in every country'], 0, 'Written summaries reduce misunderstandings.'],
    ['Repeating back what you heard is called:', ['Checking understanding', 'Interrupting', 'Arguing', 'Translating'], 0, 'It helps catch errors early.'],
    ['Which is best when writing to teammates who use English as a second language?', ['Avoid idioms and explain abbreviations', 'Use as much slang as possible', 'Write very long paragraphs', 'Use only abbreviations'], 0, 'Clear, simple writing includes everyone.'],
   ], 'assignment' => null],
  ['title' => 'Working across borders online', 'desc' => 'Plan inclusive meetings across countries, time zones and connection limits, and repair misunderstandings.', 'hours' => 3,
   'lessons' => [
    ['reading', 'Time zones and connectivity', 12, "Uganda, Kenya and Tanzania use East Africa Time (UTC+3). Rwanda and Burundi use Central Africa Time (UTC+2), one hour behind. A meeting at \"3 pm\" can easily be misunderstood, so always write the time zone and, if useful, both local times.\n\nNot everyone has fast internet. Offer an audio-only option, share slides in advance, keep files small, and record decisions in writing so people who miss a call can catch up.", false],
    ['reading', 'Repairing misunderstandings', 12, "Misunderstandings will happen. Handle them calmly.\n\n1. Assume good intent, then check facts.\n2. Describe what happened without blame: \"I understood the deadline was Monday.\"\n3. Ask how the other person saw it.\n4. Agree on what you will each do differently next time.\n\nA quick private message is often better than correcting someone in front of the group.", false],
    ['discussion', 'Plan a meeting', 10, "Your team has members in Kampala, Nairobi, Dar es Salaam and Kigali. Propose a meeting time, write it with both time zones, and list two things you would do to include people with weak connections.", false],
   ],
   'quiz' => [
    ['When it is 3 pm in Kampala, what time is it in Kigali?', ['2 pm', '3 pm', '4 pm', '5 pm'], 0, 'Rwanda uses Central Africa Time, UTC+2, one hour behind East Africa Time.'],
    ['What helps teammates with weak connections?', ['An audio-only option and notes shared in writing', 'Requiring video for everyone', 'Sending very large files', 'Holding the call without notice'], 0, 'Low-bandwidth options make participation fair.'],
    ['What is a good first step when you disagree after a misunderstanding?', ['Describe what happened without blame and ask for their view', 'Complain to the whole group', 'Stop talking to them', 'Assume they lied'], 0, 'Calm, specific conversation repairs trust.'],
    ['Why write the time zone with the meeting time?', ['Different countries may use different times', 'It looks professional only', 'It is a legal rule', 'It makes the message longer'], 0, 'Time zones differ within the region.'],
   ],
   'assignment' => ['A misunderstanding and what I learned', "Think of a real misunderstanding you had with someone from a different background, language or way of working (you may change names).\n\nIn 150 to 250 words describe what happened, what each person may have expected, and how you would handle it now using ideas from this course."]],
 ],
],

// =====================================================================================
[
 'title' => 'Teamwork and Remote Collaboration', 'category' => 'Collaboration & Teamwork', 'level' => 'Beginner', 'hours' => 3, 'color' => '#1f8a4c',
 'owner' => 'grace@youthconnect.test',
 'summary' => 'Learn how effective teams plan, divide work, communicate and give feedback, even when members are in different countries.',
 'description' => "Great ideas fail without good teamwork. This course prepares you for Level Two of YouthConnect, where you will work in multi-country teams on real projects.\n\nYou will learn how teams agree on roles and rules, how to plan with tasks and milestones, and how to collaborate on a slow connection. Completing this course unlocks team projects on the platform.",
 'learn' => "Agree team roles, goals and working rules\nBreak a project into tasks and milestones\nDefine what \"done\" means for each task\nCollaborate remotely with limited bandwidth\nGive and receive feedback constructively",
 'skills' => 'Teamwork, Project planning, Remote collaboration, Feedback, Time management',
 'weeks' => [
  ['title' => 'How good teams work', 'desc' => 'Set a shared goal, clear roles and simple team rules.', 'hours' => 3,
   'lessons' => [
    ['reading', 'Goals and roles', 10, "Strong teams know where they are going and who does what.\n\n- A shared goal: one sentence everyone can repeat.\n- Clear roles: for example team lead, note-taker, researcher, and tester. Roles can rotate.\n- Complementary skills: the best teams mix disciplines. A farmer-turned-agronomist, a developer, a data analyst and an environmental scientist can solve problems none could solve alone.", true],
    ['reading', 'Team agreements', 10, "A team agreement is a short document written together at the start. It might say how often you will meet, which channel you use for urgent messages, how fast you will reply, and how you will make decisions.\n\nWriting rules early prevents arguments later. Review the agreement after a few weeks and improve it.", false],
    ['discussion', 'Best and worst team', 8, "Describe the best team you have been part of, and one thing that made it work. Then name one habit that harmed a team you were in.", false],
   ],
   'quiz' => [
    ['A shared team goal should be:', ['Clear enough that everyone can repeat it', 'Known only by the leader', 'Changed every day', 'Kept secret'], 0, 'A clear goal aligns everyone.'],
    ['Why include different disciplines in one team?', ['Their different skills help solve problems together', 'To make the team larger only', 'To avoid planning', 'To avoid communication'], 0, 'Complementary skills make teams stronger.'],
    ['A team agreement is best written:', ['Together, at the start of the project', 'By one person in secret', 'After the project ends', 'Never'], 0, 'Early, shared rules prevent conflict.'],
    ['Which is NOT usually part of a team agreement?', ['Each member\'s private passwords', 'Meeting times', 'Reply times', 'How decisions are made'], 0, 'Passwords are private and never belong in a shared document.'],
   ], 'assignment' => null],
  ['title' => 'Planning and tracking work', 'desc' => 'Break a project into tasks and milestones and agree what "done" means.', 'hours' => 3,
   'lessons' => [
    ['reading', 'Tasks and milestones', 12, "A big project feels easier when it is split up.\n\n- A task is a small piece of work one person can finish, such as \"Interview three farmers.\"\n- A milestone is a major checkpoint made of several tasks, such as \"Prototype ready for testing.\"\n\nGive every task one owner and a due date. Many owners means no owner.", false],
    ['reading', 'Definition of done and simple boards', 12, "Agree what \"done\" means. \"Write the survey\" is done when it is reviewed by one teammate and tested with two people.\n\nA simple board with three columns, To do, In progress and Done, shows everyone the state of the work at a glance. Update it as you go. Small, frequent updates keep a remote team in sync.", false],
    ['discussion', 'Break it down', 10, "Pick a goal for a community project, such as setting up a school recycling programme. List five tasks and one milestone.", false],
   ],
   'quiz' => [
    ['What is a milestone?', ['A major checkpoint made up of several tasks', 'A single email', 'A team member', 'A meeting room'], 0, 'Milestones mark important progress points.'],
    ['Each task should have:', ['One clear owner and a due date', 'Everyone as owner', 'No due date', 'A secret owner'], 0, 'Clear ownership drives action.'],
    ['A "definition of done" helps because it:', ['Gives everyone the same standard for finished work', 'Makes work slower', 'Removes the need for review', 'Is only for managers'], 0, 'It avoids confusion about what finished means.'],
    ['A simple three-column board shows:', ['What is to do, in progress and done', 'Only completed tasks', 'Passwords', 'Weather'], 0, 'It makes progress visible.'],
   ], 'assignment' => null],
  ['title' => 'Collaborating remotely', 'desc' => 'Communicate well, give useful feedback and handle conflict when you cannot meet in person.', 'hours' => 3,
   'lessons' => [
    ['reading', 'Communication on a limited connection', 12, "Choose the lightest tool that does the job.\n\n- Short text messages for quick questions.\n- Written updates for decisions, so they are not lost.\n- Audio calls instead of video when bandwidth is weak.\n- Compress images and documents before sending, and avoid sending large files to everyone.\n\nWrite messages so they can be understood later without the whole chat history.", false],
    ['reading', 'Feedback and conflict', 14, "Feedback helps a team improve. Make it specific, kind and about the work, not the person.\n\nUse a simple pattern: what you observed, the effect it had, and what you suggest. For example: \"The report arrived after the deadline, so we could not review it. Could we agree to share drafts two days earlier?\"\n\nWhen conflict appears, talk early, listen first, focus on the problem, and ask a facilitator or mentor if you are stuck.", false],
    ['discussion', 'Give kind feedback', 8, "Rewrite this harsh comment as helpful feedback: \"Your slides are terrible.\" Post your version.", false],
   ],
   'quiz' => [
    ['When your connection is weak, which option is best for a team discussion?', ['An audio call or short text updates', 'A video call for everyone', 'Sending large videos', 'Waiting until everyone has fast internet'], 0, 'Use the lightest tool that works for everyone.'],
    ['Good feedback is:', ['Specific, kind and about the work', 'About the person\'s character', 'Vague', 'Shared only with others'], 0, 'Focus on the work and suggest improvements.'],
    ['Why record decisions in writing?', ['So people who missed the call can catch up', 'To avoid talking', 'To impress managers', 'To make messages long'], 0, 'Written records keep everyone aligned.'],
    ['If a conflict continues in your team, you should:', ['Ask a mentor or facilitator for help', 'Ignore it forever', 'Leave the team immediately', 'Insult the person'], 0, 'Seeking support early is a sign of maturity.'],
   ],
   'assignment' => ['Our team agreement', "Imagine your team of four from Uganda, Kenya, Tanzania and Rwanda is about to start a project.\n\nWrite a team agreement of 150 to 250 words covering: your shared goal, how you will communicate (including what to do if someone has weak internet), how often you will meet and in which time zone, how decisions will be made, and how you will give feedback."]],
 ],
],

// =====================================================================================
[
 'title' => 'Employability: CVs, Interviews and Online Presence', 'category' => 'Employability & Career Skills', 'level' => 'Beginner', 'hours' => 3, 'color' => '#b3261e',
 'owner' => 'david@youthconnect.test',
 'summary' => 'Identify your strengths, write a strong CV, perform well in interviews and build a professional online profile.',
 'description' => "Many young people have real skills but struggle to show them to employers. This course turns your experience, including volunteering, study and community work, into a clear story.\n\nYou will write a focused CV, practise answering interview questions with the STAR method, and set up a professional online presence.",
 'learn' => "List your skills and transferable strengths\nWrite a clear one- to two-page CV tailored to a job\nAnswer interview questions using the STAR method\nPrepare questions to ask an employer\nBuild a professional online profile safely",
 'skills' => 'CV writing, Interview skills, Personal branding, Networking, Goal setting',
 'weeks' => [
  ['title' => 'Know your strengths', 'desc' => 'Turn your experience into skills employers value.', 'hours' => 3,
   'lessons' => [
    ['reading', 'Skills you already have', 10, "Skills come from jobs, school, volunteering, family responsibilities and projects. Technical skills are things like using spreadsheets or repairing phones. Transferable skills such as communication, problem solving, teamwork and reliability are useful in almost any job.\n\nExample: organising a church or community event shows planning, budgeting and teamwork.", true],
    ['reading', 'Setting career goals', 10, "Choose a direction before you apply. Ask yourself: What work do I enjoy? What does the local or regional market need? What skill should I build next?\n\nWrite one goal for six months and one for two years. Make them specific, for example \"Get an internship in data entry or analysis by June.\"", false],
    ['discussion', 'Three strengths', 8, "List three strengths and one real example for each. Share one in the discussion and encourage a classmate by naming a strength you notice in their post.", false],
   ],
   'quiz' => [
    ['Which is a transferable skill?', ['Communication', 'Knowing one particular software menu only', 'A hobby you never use', 'A secret'], 0, 'Communication applies to nearly every job.'],
    ['Organising a community event can show:', ['Planning and teamwork', 'Nothing useful', 'Only luck', 'Only free time'], 0, 'Volunteer experience builds real skills.'],
    ['A good career goal is:', ['Specific, with a time frame', 'Vague', 'Secret', 'Impossible by definition'], 0, 'Clear goals guide action.'],
    ['Before applying for jobs, you should first:', ['Understand your strengths and the direction you want', 'Send the same CV everywhere', 'Wait for luck', 'Avoid planning'], 0, 'Self-knowledge helps you target well.'],
   ], 'assignment' => null],
  ['title' => 'CVs and applications', 'desc' => 'Write a focused CV and a short cover letter tailored to the job.', 'hours' => 3,
   'lessons' => [
    ['reading', 'Structure of a strong CV', 12, "Keep your CV to one or two pages, in a clean layout with a readable font.\n\n- Contact details and a short profile (two or three lines).\n- Work, volunteering or project experience, newest first.\n- Education and training.\n- Skills and languages.\n\nUse action words and numbers: \"Organised a clean-up for 60 volunteers and collected 40 bags of waste.\" Check spelling twice.", true],
    ['reading', 'Tailoring and cover letters', 12, "Read the job advert carefully and use its key words where they honestly describe you. Change your profile to fit each role.\n\nA cover letter is three short paragraphs: why you are writing, why you fit the role with one or two examples, and a polite close. Address a named person if you can.", false],
    ['discussion', 'Improve a bullet point', 8, "Rewrite this weak CV line to include an action and a number: \"Helped at a shop.\" Post your version.", false],
   ],
   'quiz' => [
    ['Which line is strongest on a CV?', ['"Trained 25 farmers in mobile banking basics."', '"Responsible for stuff."', '"Good worker."', '"Helped."'], 0, 'Specific actions with numbers show impact.'],
    ['How long should a CV for an early-career applicant normally be?', ['One to two pages', 'Ten pages', 'Half a line', 'Unlimited'], 0, 'Short and focused is best.'],
    ['Tailoring your CV means:', ['Adjusting it to fit the job you are applying for', 'Sending the same one to everyone', 'Adding false skills', 'Removing your name'], 0, 'Tailoring shows you read the advert and fit it.'],
    ['A good cover letter is:', ['Short and specific to the role', 'Very long and general', 'Copied from the internet', 'Left out always'], 0, 'Keep it clear and relevant.'],
   ], 'assignment' => null],
  ['title' => 'Interviews and online presence', 'desc' => 'Answer questions with the STAR method and build a professional profile safely.', 'hours' => 3,
   'lessons' => [
    ['reading', 'The STAR method', 12, "Interviewers often ask for a time you did something, such as \"Tell me about a time you solved a problem.\" Use STAR to answer clearly.\n\n- Situation: the context in one or two sentences.\n- Task: what you needed to achieve.\n- Action: what YOU did, step by step.\n- Result: the outcome, with a number if possible, and what you learned.\n\nPractise aloud. Also prepare two questions to ask the employer, such as how success is measured in the role.", false],
    ['reading', 'Your professional online presence', 12, "Employers often search for candidates online. Use a professional photo, a clear headline and an accurate summary on a professional networking profile. Review what is public on social media and remove content you would not show an employer.\n\nBe careful with scams: a genuine employer will never ask you to pay for a job. Never send ID numbers or bank details before you have verified the company.", false],
    ['discussion', 'Practise a STAR story', 12, "Write a short STAR answer about a time you solved a problem or worked in a team. Share it and give one kind suggestion to a classmate.", false],
   ],
   'quiz' => [
    ['What does STAR stand for?', ['Situation, Task, Action, Result', 'Skill, Time, Answer, Reply', 'Start, Talk, Ask, Rest', 'Study, Think, Act, Repeat'], 0, 'STAR structures a clear example.'],
    ['In the "Action" step you should describe:', ['What you personally did', 'What everyone else did', 'Nothing', 'Only your feelings'], 0, 'Interviewers want to know your own contribution.'],
    ['A genuine employer will:', ['Never ask you to pay to get a job', 'Ask for a registration fee', 'Ask for your PIN', 'Ask for your password'], 0, 'Requests for payment are a classic scam.'],
    ['Before applying, your public social media should be:', ['Reviewed so nothing embarrassing is visible', 'Left alone', 'Deleted without thought', 'Full of private details'], 0, 'Employers often look at your online presence.'],
   ],
   'assignment' => ['My STAR answer', "Write a STAR answer of 150 to 250 words to this interview question: \"Tell me about a time you worked in a team to solve a problem.\"\n\nLabel the four parts: Situation, Task, Action, Result. Use a real example from school, work, volunteering or community life."]],
 ],
],

];
