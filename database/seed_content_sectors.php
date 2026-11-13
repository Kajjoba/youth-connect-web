<?php
// Sector courses (agriculture, health, energy, business, creative, data). Same format as seed_content.php.
// Helpers keep this file compact: $R reading, $D discussion, $Q quiz question, $W week.
$R = fn($title, $mins, $body, $preview = false) => ['reading', $title, $mins, $body, $preview];
$D = fn($title, $prompt) => ['discussion', $title, 6, $prompt, false];
$Q = fn($q, $opts, $correct, $why) => [$q, $opts, $correct, $why];
$W = fn($title, $desc, $lessons, $quiz, $assignment = null) => ['title' => $title, 'desc' => $desc, 'hours' => 3, 'lessons' => $lessons, 'quiz' => $quiz, 'assignment' => $assignment];

return [

// ============================== AGRICULTURE & FOOD ==============================
[
 'title' => 'Climate-Smart Farming Basics', 'category' => 'Agriculture & Food', 'level' => 'Beginner', 'hours' => 3, 'color' => '#2e7d32',
 'owner' => 'david@youthconnect.test',
 'summary' => 'Grow more with less risk: healthy soil, smart water use and simple ways to adapt your farm to changing weather.',
 'description' => "Rainfall is less predictable than it used to be, and many young farmers in East Africa feel it first. This course shows low-cost practices that protect harvests and soil.\n\nIn three weeks you will learn how to read your soil, save and manage water, and plan crops around weather risk. You finish with a one-page plan for a real plot, whether it is a family farm, a school garden or a community project.",
 'learn' => "Explain what makes soil healthy and how to improve it cheaply\nUse mulching, terraces and water harvesting to cope with dry spells and heavy rain\nChoose crops and planting dates to reduce weather risk\nUse rotation and intercropping to protect soil and income\nWrite a simple climate-smart plan for a plot",
 'skills' => 'Soil health, Water management, Crop planning, Climate adaptation, Farm planning',
 'weeks' => [
  $W('Healthy soil, healthy harvest', 'Understand what soil is made of and the cheap ways to make it better.', [
    $R('What is in your soil?', 10, "Soil is more than dirt. It holds minerals, air, water, tiny living organisms and organic matter (rotted plants and manure). Roots need all of these.\n\n## A simple field test\nTake a handful of moist soil and squeeze it. If it forms a ball that crumbles when poked, it is a good loam. If it stays sticky like clay, water drains slowly. If it falls apart like sand, it dries out fast.\n\nDark, crumbly soil that smells earthy usually has plenty of organic matter.", true),
    $R('Feeding the soil', 12, "Soil that is farmed without being fed gets tired.\n\n- **Compost and manure** add nutrients and help soil hold water.\n- **Mulch** (dry grass or crop residue on the surface) cools the soil, stops weeds and reduces evaporation.\n- **Legumes** such as beans and groundnuts add nitrogen when grown in rotation.\n- **Avoid bare soil**: rain washes it away. Keep it covered.\n\nBurning residues destroys organic matter. Mixing it back into the soil keeps it."),
    $D('Your soil', 'Describe the soil where you live or farm (colour, texture, how it behaves after rain). What one change would you try first?'),
   ], [
    $Q('Which of these adds organic matter to soil?', ['Compost and manure', 'Burning crop residues', 'Leaving soil bare', 'Removing all plant cover'], 0, 'Compost and manure feed soil organisms and improve structure.'),
    $Q('What is the main benefit of mulch?', ['It cools soil, reduces evaporation and suppresses weeds', 'It makes weeds grow faster', 'It stops rain entering the soil', 'It removes nutrients'], 0, 'Mulch protects the surface and keeps moisture in.'),
    $Q('Why are beans and groundnuts useful in rotation?', ['They add nitrogen to the soil', 'They use up all the water', 'They kill every pest', 'They need no sunlight'], 0, 'Legumes fix nitrogen, which helps the next crop.'),
    $Q('Soil that stays sticky and drains slowly is most likely:', ['High in clay', 'Pure sand', 'Full of stones', 'Made of compost only'], 0, 'Clay soils hold water and drain slowly.'),
  ]),
  $W('Water: too little, too much', 'Save rain, use it wisely and protect fields from erosion.', [
    $R('Harvesting and saving water', 12, "Water is the biggest climate risk for most farms. The goal is to slow water down, spread it out and let it soak in.\n\n- **Planting pits and trenches** catch rain on dry land.\n- **Terraces and contour bunds** (low ridges along the slope) stop soil being washed downhill.\n- **Rooftop harvesting** stores rain in tanks for dry weeks.\n- **Mulch and shade** reduce losses from evaporation.\n\nCheapest first: mulch and planting along the contour cost almost nothing.", true),
    $R('Using water wisely', 10, "Water the root zone, not the leaves, and water early morning or late evening to reduce evaporation.\n\n## Drip and bottle systems\nA bottle with a small hole placed next to a seedling delivers water slowly. Simple drip kits use up to half the water of hand watering.\n\n## Too much rain\nHeavy rain needs drainage channels and covered soil. Plant grass strips along channels to slow the water."),
    $D('Water on your land', 'Where does water go on a plot you know after a heavy storm? Suggest one way to slow it down.'),
   ], [
    $Q('What do contour bunds do?', ['Slow runoff and reduce soil erosion on slopes', 'Increase wind speed', 'Remove nutrients', 'Dry the soil'], 0, 'Bunds follow the slope line and trap water and soil.'),
    $Q('When is the best time to water crops to reduce evaporation?', ['Early morning or late evening', 'Midday in strong sun', 'Only when it is windy', 'Never'], 0, 'Cooler hours lose less water to evaporation.'),
    $Q('Which is the cheapest way to cut water loss?', ['Mulching the soil surface', 'Buying a large pump', 'Digging up all plants', 'Planting only in the dry season'], 0, 'Mulch costs little and keeps moisture in.'),
  ]),
  $W('Planning around risk', 'Choose crops, dates and rotations that protect your income.', [
    $R('Reading the season', 10, "Farmers have always watched the sky, and now there is more help: national weather services, radio forecasts and SMS advisory services give seasonal outlooks.\n\n- Plant when rains are established, not at the first shower.\n- Choose short-season or drought-tolerant varieties if the outlook is poor.\n- Stagger planting dates so one dry spell does not hit everything.\n\nKeep a simple notebook of planting dates, rainfall and yields. After two seasons it becomes your best forecast.", true),
    $R('Rotation and mixing crops', 12, "Growing the same crop on the same plot every year builds up pests and drains the soil.\n\n## Rotation\nFollow a cereal (maize, sorghum) with a legume (beans, cowpeas), then a root or vegetable crop.\n\n## Intercropping\nGrowing two crops together, such as maize with beans, uses space well, covers soil and spreads risk: if one fails, the other may still produce.\n\nDiversity is insurance."),
    $D('Your crop mix', 'Which crops would you grow together or in rotation on a small plot, and why?'),
   ], [
    $Q('Why stagger planting dates?', ['A single dry spell will not damage every plot', 'It guarantees high prices', 'It removes the need for water', 'It kills weeds'], 0, 'Spreading dates spreads weather risk.'),
    $Q('Which sequence is a sensible rotation?', ['Maize, then beans, then vegetables', 'Maize, maize, maize', 'Bare soil every season', 'Only one crop forever'], 0, 'Alternating cereals and legumes protects soil and breaks pest cycles.'),
    $Q('What is intercropping?', ['Growing two or more crops together on the same plot', 'Selling crops at the border', 'Buying seeds on credit', 'Storing grain in sacks'], 0, 'Intercrops share space and reduce risk.'),
  ], ['My climate-smart plot plan', "Pick a real plot (home, school or community). In 150 to 250 words describe: (1) the soil and what you will do to improve it; (2) how you will save or manage water; and (3) which crops you will plant, when, and how you will spread risk."]),
 ],
],

// -------------------------------------------------------------------------------
[
 'title' => 'Post-Harvest Handling and Value Addition', 'category' => 'Agriculture & Food', 'level' => 'Beginner', 'hours' => 3, 'color' => '#b7791f',
 'owner' => 'david@youthconnect.test',
 'summary' => 'Stop losing food after harvest and earn more by drying, storing, processing and packaging your produce.',
 'description' => "In many places a large share of what farmers grow is lost between the field and the market. Reducing those losses is like growing more without planting more.\n\nThis course covers harvesting at the right time, drying and storing grain safely, and turning raw produce into products people pay more for.",
 'learn' => "Explain where and why post-harvest losses happen\nHarvest, dry and store grain and legumes safely\nKeep fresh produce like tomatoes and mangoes in better condition\nPick a simple value-addition idea and check whether it can make money\nUse clean handling and basic packaging",
 'skills' => 'Post-harvest handling, Storage, Food safety, Value addition, Packaging',
 'weeks' => [
  $W('Harvest right, dry right', 'Timing, drying and the first steps that decide quality.', [
    $R('Where losses happen', 10, "Food is lost at every step: harvesting too early or late, rough handling, slow drying, poor storage, pests and long delays to market.\n\nMost losses are preventable and many cost almost nothing to fix. Count the stages for your own crop and ask: where do we lose the most?", true),
    $R('Drying grain and beans', 12, "Wet grain grows mould, and some moulds make toxins (aflatoxin) that are harmful to people and animals.\n\n- Harvest when the crop is mature and dry in the field if possible.\n- Dry on tarpaulins or raised racks, never directly on soil.\n- Turn the grain often and protect it from rain and animals.\n- Grain is dry enough when a kernel cracks with a snap when bitten or squeezed, or when a moisture meter reads about 13 percent for maize."),
    $D('Losses you have seen', 'Tell us about food being lost after harvest in your community. What caused it?'),
   ], [
    $Q('Why must grain be dried properly?', ['Damp grain grows mould that can make harmful toxins', 'It makes the grain heavier', 'It makes it sweeter', 'It prevents all pests forever'], 0, 'Moulds thrive in moisture and some produce aflatoxin.'),
    $Q('Where should grain be dried?', ['On a tarpaulin or raised rack', 'Directly on bare soil', 'Inside a closed wet sack', 'Under a leaking roof'], 0, 'Drying off the ground keeps grain cleaner and drier.'),
    $Q('Post-harvest losses are best described as:', ['Food lost between harvest and consumption', 'Seeds lost at planting', 'Money spent on fuel', 'Rain that falls after harvest'], 0, 'They occur after the crop leaves the plant.'),
  ]),
  $W('Safe storage and fresh produce', 'Keep grain safe for months and fresh produce fresh for days.', [
    $R('Storing grain', 12, "Clean, dry grain in a clean, dry store lasts much longer.\n\n- Clean the store and sacks before filling them.\n- Use hermetic (airtight) bags or sealed containers, which starve insects of oxygen without chemicals.\n- Stack sacks on pallets away from walls.\n- Check regularly for heat, damp smell or insect dust.\n\nNever mix new grain with old grain.", true),
    $R('Fresh fruit and vegetables', 12, "Fresh produce is alive and keeps losing water and quality after picking.\n\n- Harvest in the cool morning and keep produce in the shade.\n- Handle gently: bruises start rot.\n- Use clean, ventilated crates instead of heavy sacks.\n- Sort out damaged items so they do not spoil the rest.\n\nEvery hour of shade and every extra cushion of care is money saved."),
    $D('Storage at home', 'How do people store grain or vegetables where you live? Which method would you improve and how?'),
   ], [
    $Q('Why do airtight bags protect grain?', ['Insects run out of oxygen', 'They make grain wetter', 'They attract more pests', 'They need daily opening'], 0, 'Hermetic storage starves insects of oxygen.'),
    $Q('Which habit reduces damage to fresh tomatoes?', ['Gentle handling in clean ventilated crates', 'Throwing them into heavy sacks', 'Leaving them in the sun', 'Mixing damaged and good ones'], 0, 'Bruising and heat speed up rot.'),
    $Q('Why stack sacks on pallets?', ['It keeps them off damp floors and allows airflow', 'It makes them heavier', 'It hides them from buyers', 'It is only for decoration'], 0, 'Air circulation and distance from damp reduce spoilage.'),
  ]),
  $W('Adding value', 'Turn raw produce into products and test whether they can earn money.', [
    $R('What is value addition?', 10, "Value addition means changing a product so it is worth more: cleaning, sorting, drying, grinding, cooking or packing.\n\nExamples: tomatoes to dried tomatoes or sauce, milk to yoghurt, cassava to flour, sunflower to cooking oil, bananas to dried chips.\n\nBefore you start, ask: who will buy it, at what price, and what does it cost me to make?", true),
    $R('Clean, safe and well packed', 12, "Customers buy what looks clean and trustworthy.\n\n- Wash hands and equipment, and use clean water.\n- Keep raw and cooked foods apart.\n- Use clean, sealed packaging with a simple label: product name, date, your name and contact.\n- Start small, get feedback and check with the local standards or health office about any permits you need."),
    $D('Your product idea', 'Pick a crop you know. What product could you make from it and who would buy it?'),
   ], [
    $Q('What does value addition mean?', ['Changing a product so it is worth more', 'Selling it cheaper', 'Planting more seeds', 'Throwing away damaged crops'], 0, 'Processing, grading and packaging raise the price a product can earn.'),
    $Q('What should you ask before starting a product?', ['Who will buy it and what does it cost to make?', 'What colour is the logo?', 'How loud is the machine?', 'Can I avoid cleaning?'], 0, 'Demand and cost decide whether it can make money.'),
    $Q('What helps a food product earn customer trust?', ['Clean preparation and a clear label', 'A very long story', 'No packaging at all', 'Handling it with dirty hands'], 0, 'Hygiene and clear information build trust.'),
  ], ['My value-addition idea', "Choose one crop and one product you could make from it. In 150 to 250 words explain: (1) how you would make it safely; (2) who the customers are; and (3) a rough cost and selling price per unit."]),
 ],
],

// -------------------------------------------------------------------------------
[
 'title' => 'Agribusiness: Farm Records, Costs and Markets', 'category' => 'Agriculture & Food', 'level' => 'Intermediate', 'hours' => 3, 'color' => '#00796b',
 'owner' => 'david@youthconnect.test',
 'summary' => 'Run your farm like a business: keep records, know your costs, find buyers and group up to earn more.',
 'description' => "Good farmers are not always profitable farmers. Profit comes from knowing your costs, choosing the right market and selling at the right time.\n\nThis course teaches simple record keeping, a gross margin calculation, how to find and compare buyers, and how groups and cooperatives can raise income.",
 'learn' => "Keep simple farm records of costs, yields and sales\nCalculate gross margin per crop or animal\nCompare markets and buyers and negotiate a fair price\nUnderstand how groups and cooperatives help small farmers\nUse mobile money safely for farm payments",
 'skills' => 'Record keeping, Costing, Marketing, Negotiation, Cooperatives',
 'weeks' => [
  $W('Know your numbers', 'Records, costs and what you actually earn.', [
    $R('Why keep records', 10, "If you do not write it down, you cannot know if you are making money. Records show which crops pay, where money leaks and what to change.\n\nA notebook is enough. Record: date, activity, money in, money out, quantities and who you sold to.", true),
    $R('Costs and gross margin', 12, "**Gross margin = income from sales minus the direct costs of producing it.**\n\nExample, one acre of beans:\n- Sales: 600 kg at 2,500 per kg = 1,500,000\n- Direct costs: seed, labour, fertiliser and transport = 900,000\n- Gross margin = 600,000\n\nCompare gross margins between crops to decide where to put your land and time. Do not forget your own labour: it has a value."),
    $D('One crop, real numbers', 'Pick a crop you know. List its main costs and a rough price. Does it look profitable?'),
   ], [
    $Q('Gross margin equals:', ['Sales minus direct production costs', 'Sales plus costs', 'Only the cost of seed', 'The price per kilogram'], 0, 'It shows what is left after direct costs.'),
    $Q('Why keep farm records?', ['To see which activities make or lose money', 'To impress neighbours', 'To avoid paying anything', 'Records are never useful'], 0, 'Records guide better decisions.'),
    $Q('A farmer earns 1,000,000 from sales and spends 700,000 on direct costs. The gross margin is:', ['300,000', '1,700,000', '700,000', '1,000,000'], 0, '1,000,000 minus 700,000 equals 300,000.'),
  ]),
  $W('Finding and keeping buyers', 'Where to sell, how to compare buyers and what a fair deal looks like.', [
    $R('Know your market', 12, "Different buyers pay different prices: roadside traders, local markets, schools and hotels, processors, supermarkets and exporters.\n\nCompare them on: price, quantity needed, quality required, payment time and transport cost. A slightly lower price paid on the spot can beat a higher price paid after three months.", true),
    $R('Negotiating and trust', 10, "Know your costs before you negotiate so you know your lowest acceptable price.\n\n- Ask what quality and quantity the buyer needs.\n- Agree price, date and payment method clearly, in writing if possible (a message is a record).\n- Deliver what you promised. Repeat customers are worth more than one big sale."),
    $D('Where would you sell?', 'List three possible buyers for a crop you grow. Which would you pick and why?'),
   ], [
    $Q('Which factor should you compare when choosing a buyer?', ['Price, payment time, quality required and transport cost', 'Only the buyer\'s phone colour', 'Only the shop name', 'Nothing, take the first offer'], 0, 'The best buyer is the one with the best total deal.'),
    $Q('Why know your costs before negotiating?', ['You can tell your lowest acceptable price', 'It lets you avoid talking', 'It makes the buyer pay more automatically', 'It is not important'], 0, 'Costs set the floor for your price.'),
    $Q('Why agree payment terms clearly?', ['To avoid disputes and late payment', 'To hide the price', 'To make deals longer', 'Because buyers dislike clarity'], 0, 'Clear terms protect both sides.'),
  ]),
  $W('Stronger together', 'Groups, cooperatives, savings and safe mobile money.', [
    $R('Why farmers group together', 12, "Alone, a small farmer has little bargaining power. Together, farmers can buy inputs in bulk, share transport, meet buyer volumes and gain training or loans.\n\nGood groups have clear rules, regular meetings, written records and shared leadership. Trust and transparency matter more than size.", true),
    $R('Savings and mobile money', 10, "Village savings groups (VSLAs) help members save regularly and borrow small loans.\n\nMobile money makes payments safer than carrying cash. Protect it: never share your PIN, confirm the recipient name before sending and keep transaction messages as records."),
    $D('A group that works', 'Have you seen a farmers\' group or savings group succeed or fail? What made the difference?'),
   ], [
    $Q('What is one advantage of selling as a group?', ['You can meet large buyer volumes and bargain better', 'You must accept the lowest price', 'You avoid all costs', 'You cannot grow crops'], 0, 'Volume and organisation create bargaining power.'),
    $Q('What keeps a savings group healthy?', ['Clear rules and written records', 'Secret decisions', 'No meetings', 'Only the leader knowing the balance'], 0, 'Transparency builds trust.'),
    $Q('Which mobile money habit is safe?', ['Never sharing your PIN', 'Telling friends your PIN', 'Sending without checking the name', 'Deleting all receipts'], 0, 'Keep your PIN private and confirm recipients.'),
  ], ['Farm business snapshot', "Choose a crop or animal enterprise you know. In 150 to 250 words give: (1) the main costs and expected income for one season, with the gross margin; (2) two possible buyers and which you would choose; and (3) one way a group could improve your result."]),
 ],
],

// -------------------------------------------------------------------------------
[
 'title' => 'Poultry and Small Livestock Basics', 'category' => 'Agriculture & Food', 'level' => 'Beginner', 'hours' => 3, 'color' => '#c05621',
 'owner' => 'david@youthconnect.test',
 'summary' => 'Start small with chickens, goats or rabbits: housing, feeding, disease prevention and the numbers that keep it profitable.',
 'description' => "Small livestock are one of the quickest ways for a young person to start earning in agriculture: they need little land and grow fast. But poor housing, feeding and hygiene can wipe out a flock in days.\n\nThis course covers the basics of housing, feeding and health for poultry and small livestock, and how to work out whether your enterprise pays.",
 'learn' => "Plan safe, clean housing for chickens and small animals\nFeed animals correctly and give clean water\nPrevent common diseases with hygiene, vaccination and early action\nKeep simple production and cost records\nKnow when to call a veterinary or extension officer",
 'skills' => 'Animal husbandry, Animal health, Feeding, Biosecurity, Enterprise records',
 'weeks' => [
  $W('Housing and feeding', 'A good start: the right house, the right feed, clean water.', [
    $R('A good house', 12, "A house protects animals from rain, wind, heat, thieves and predators and keeps them dry.\n\n- Good ventilation but no draughts at animal level.\n- Dry floor with clean bedding (dry grass or wood shavings).\n- Enough space: crowded animals fight, stress and spread disease.\n- Easy to clean, with separate space for young animals.", true),
    $R('Feed and water', 12, "Animals need energy, protein, minerals and above all clean water. Layers and broilers need special feeds for their stage; goats and rabbits need forage plus a little supplement.\n\n- Change feed slowly, not suddenly.\n- Store feed dry; mouldy feed makes animals sick.\n- Clean drinkers daily. Water is the most important feed.\n\nFeed is usually 60 to 70 percent of the cost, so waste matters."),
    $D('Your starter flock', 'If you started with 20 chickens or 5 goats, what would you need to prepare first?'),
   ], [
    $Q('Which is the most important feed for animals?', ['Clean water', 'Sweets', 'Sand', 'Anything left over'], 0, 'Animals can survive longer without feed than without water.'),
    $Q('A good animal house should be:', ['Dry, ventilated and easy to clean', 'Damp and dark', 'Closed without air', 'Crowded'], 0, 'Dry, clean, airy housing reduces disease.'),
    $Q('Why store feed in a dry place?', ['Damp feed grows mould that can make animals sick', 'Dry feed is heavier', 'Moulds are harmless', 'It does not matter'], 0, 'Mould and toxins are harmful.'),
  ]),
  $W('Keeping animals healthy', 'Biosecurity, vaccination and spotting problems early.', [
    $R('Biosecurity: keep disease out', 12, "Most diseases arrive on boots, hands, equipment, new animals and visitors.\n\n- Keep a footbath or change shoes at the door.\n- Isolate new or sick animals for at least two weeks.\n- Do not share equipment between farms without cleaning.\n- Remove dead birds and animals quickly and bury or burn them properly.\n\nPrevention is cheaper than treatment.", true),
    $R('Vaccination and early signs', 12, "Follow a vaccination schedule from a vet or extension officer. For chickens this usually includes Newcastle disease and Gumboro.\n\nWatch daily: animals that stay away from the group, stop eating, have watery droppings, difficulty breathing, swollen faces or sudden deaths need action. Isolate them and call a vet or extension officer quickly."),
    $D('A health scare', 'Have you seen sick animals on a farm? What was done and what would you do differently?'),
   ], [
    $Q('What is biosecurity?', ['Practices that keep disease out of your farm', 'A type of feed', 'A type of animal', 'A market price'], 0, 'Biosecurity stops diseases from entering and spreading.'),
    $Q('What should you do with a new animal bought from another farm?', ['Keep it separate for a couple of weeks first', 'Mix it in immediately', 'Never feed it', 'Ignore it'], 0, 'Quarantine reveals disease before it spreads.'),
    $Q('Which sign needs quick action?', ['Several animals suddenly stop eating and breathe with difficulty', 'An animal sleeping at night', 'An animal drinking water', 'An animal resting briefly'], 0, 'Sudden group illness can mean a serious disease.'),
  ]),
  $W('Does it pay?', 'Records, costs, selling and growing step by step.', [
    $R('Simple records', 10, "Record every day or week: animals in and out, deaths, feed used, eggs or weight, vaccines and money spent or earned.\n\nFrom this you can calculate cost per egg or per kilogram and spot problems quickly: a sudden drop in eggs, for example, tells you something is wrong.", true),
    $R('Growing the business', 12, "Start small and learn before you expand. Reinvest profits step by step.\n\n- Know your selling price before you stock: eggs, live birds, manure and meat each have different markets.\n- Plan cash for feed until the first sales.\n- Sell regularly to trusted buyers: restaurants, schools, shops, neighbours.\n- Join a group to share vets, bulk feed purchases and transport."),
    $D('Your first numbers', 'Estimate monthly feed cost and expected income for a small flock. Would it pay?'),
   ], [
    $Q('Why keep daily records?', ['To spot problems early and know your costs', 'To waste time', 'To hide losses', 'They are never needed'], 0, 'Records show trends and costs.'),
    $Q('The safest way to grow an animal enterprise is to:', ['Start small, learn and reinvest profits', 'Borrow heavily on day one', 'Stock as many as possible at once', 'Ignore disease'], 0, 'Learning at small scale limits risk.'),
    $Q('Which is usually the biggest cost for chickens?', ['Feed', 'Paint', 'Flags', 'Signboards'], 0, 'Feed is commonly the largest share of cost.'),
  ], ['My small livestock plan', "Pick poultry, goats or rabbits. In 150 to 250 words explain: (1) housing and feeding for your starting number; (2) three health practices you will follow; and (3) your expected costs and income for the first three months."]),
 ],
],

// ============================== HEALTH & WELLBEING ==============================
[
 'title' => 'First Aid and Community Health Basics', 'category' => 'Health & Wellbeing', 'level' => 'Beginner', 'hours' => 3, 'color' => '#c62828',
 'owner' => 'grace@youthconnect.test',
 'summary' => 'Know what to do in the first minutes of an emergency and how to help your community stay healthy.',
 'description' => "In an emergency the first few minutes matter, often before any health worker arrives. This course gives you simple, widely recommended first aid skills and practical habits that prevent illness in homes and communities.\n\nIt is an introduction, not a replacement for a certified first aid course or medical care. Always call emergency services or go to a health facility for serious conditions.",
 'learn' => "Check a scene for safety and call for help properly\nTreat bleeding, burns, cuts and choking with basic first aid\nRecognise when someone needs urgent medical help\nPractise handwashing, safe water and food hygiene\nShare health messages respectfully in your community",
 'skills' => 'First aid, Emergency response, Hygiene, Health communication, Prevention',
 'weeks' => [
  $W('Emergencies: the first minutes', 'Stay safe, call for help and handle the most common emergencies.', [
    $R('Check, call, care', 10, "When something happens, follow three steps.\n\n1. **Check** the scene: is it safe for you? Do not become a second casualty.\n2. **Call** for help early. Know your local emergency number and the nearest health facility, and send someone to bring help.\n3. **Care** for the person with the skills below until help arrives.\n\nStay calm and speak clearly: where you are, what happened, how many people are hurt.", true),
    $R('Bleeding and burns', 12, "**Bleeding:** press firmly on the wound with a clean cloth and keep pressing. Raise the injured limb if possible. Do not remove the cloth if it soaks through; add more on top.\n\n**Burns:** cool the burn under clean running water for 20 minutes. Remove rings or tight clothing nearby before it swells. Cover loosely with a clean non-fluffy cloth. Do not use butter, toothpaste or ointments, and do not burst blisters. Seek medical care for large, deep or face burns."),
    $D('Prepared at home?', 'What is in your home or school first aid kit, and what is missing? Share the nearest health facility number you would call.'),
   ], [
    $Q('What is the first thing to do at an emergency scene?', ['Check that it is safe for you', 'Move the person at once', 'Leave immediately', 'Take photos'], 0, 'Your safety comes first so you can help.'),
    $Q('How do you treat a burn?', ['Cool under clean running water for about 20 minutes', 'Apply butter', 'Burst the blisters', 'Cover with toothpaste'], 0, 'Running water cools the burn and reduces damage.'),
    $Q('How do you control heavy bleeding?', ['Press firmly with a clean cloth and keep pressing', 'Wash it again and again', 'Remove the cloth every minute', 'Leave it uncovered'], 0, 'Firm continuous pressure helps stop bleeding.'),
  ]),
  $W('Choking, fainting, fever and seizures', 'Know the signs, act calmly and know when it is urgent.', [
    $R('Choking and fainting', 12, "**Choking:** if a person cannot cough, speak or breathe, give up to five firm back blows between the shoulder blades, then up to five abdominal thrusts. Call for help. Follow a trained provider's guidance for babies and small children.\n\n**Fainting:** lay the person down, raise their legs and loosen tight clothes. If they do not wake quickly or have trouble breathing, get urgent help.", true),
    $R('Fever, seizures and when to go to hospital', 12, "**Fever:** give fluids, rest and light clothing. Go to a health facility for very high fever, fever in babies or fever with stiff neck, confusion or rash. In malaria areas, get tested.\n\n**Seizures:** clear space, cushion the head, do not hold the person or put anything in the mouth, and time it. After it stops, place them on their side. Call for help if it lasts more than five minutes or happens repeatedly."),
    $D('Myths and facts', 'Which first aid myths have you heard (for example putting things in the mouth during a seizure)? What is the safer advice?'),
   ], [
    $Q('What should you NOT do during a seizure?', ['Put anything in the person\'s mouth', 'Clear space around them', 'Cushion their head', 'Time the seizure'], 0, 'Objects in the mouth can cause injury and block breathing.'),
    $Q('A person cannot cough, speak or breathe because of choking. You should:', ['Give back blows and abdominal thrusts and call for help', 'Give them water', 'Wait quietly', 'Slap their face'], 0, 'Back blows and thrusts can clear the airway.'),
    $Q('A baby has a fever. The safest action is to:', ['Seek care at a health facility', 'Ignore it', 'Give adult medicines', 'Wrap them tightly in many blankets'], 0, 'Fever in babies needs professional assessment.'),
  ]),
  $W('Healthy homes and communities', 'Hygiene, safe water and talking about health.', [
    $R('Hands, water and food', 12, "Many illnesses, including diarrhoea and cholera, spread through dirty hands, unsafe water and unclean food.\n\n- Wash hands with soap and water after the toilet and before eating or preparing food.\n- Treat drinking water if unsure: boiling, chlorine tablets or safe filters.\n- Cook food thoroughly, cover it, and keep raw and cooked foods apart.\n- Use a clean latrine and keep it away from water sources.", true),
    $R('Sharing health messages', 10, "People listen to people they trust. When sharing health information:\n\n- Use simple words in the local language.\n- Show, not only tell: demonstrate handwashing.\n- Listen first and respect beliefs.\n- Use trusted sources (the Ministry of Health, WHO, local clinics) and say when you are not sure.\n- Never spread rumours or share private health information."),
    $D('Your community', 'What one health habit would make the biggest difference in your community and how would you share it?'),
   ], [
    $Q('When should you wash your hands with soap?', ['After the toilet and before preparing or eating food', 'Only on holidays', 'Never', 'Only when they look muddy'], 0, 'These are key moments for stopping germs spreading.'),
    $Q('Which is a trustworthy source of health information?', ['The Ministry of Health or your local clinic', 'An unknown forwarded message', 'A rumour', 'A random comment'], 0, 'Use official or professional sources.'),
    $Q('Why should you keep raw and cooked food apart?', ['To stop germs spreading from raw to cooked food', 'To save plates', 'To change the taste', 'It does not matter'], 0, 'Cross-contamination causes food poisoning.'),
  ], ['My community health message', "Pick one health habit (handwashing, safe water, food hygiene or first aid). In 150 to 250 words explain: (1) who your audience is; (2) how you would teach it in their language; and (3) how you would know it worked."]),
 ],
],

// -------------------------------------------------------------------------------
[
 'title' => 'Mental Health and Wellbeing for Young People', 'category' => 'Health & Wellbeing', 'level' => 'Beginner', 'hours' => 3, 'color' => '#6a4c93',
 'owner' => 'grace@youthconnect.test',
 'summary' => 'Understand stress, build healthy habits and support friends, without stigma.',
 'description' => "Young people carry real pressure: exams, jobs, money, family expectations and social media. Looking after your mind is as important as looking after your body.\n\nThis course gives practical tools for coping with stress, building routines and supporting others. It is for learning and is not a substitute for professional care. If you or someone is in danger, contact local emergency services or a trusted adult straight away.",
 'learn' => "Recognise common signs of stress, anxiety and low mood\nUse simple habits for sleep, movement, connection and calm\nListen to a friend in difficulty without judging\nKnow when and where to find professional help\nUse social media in healthier ways",
 'skills' => 'Stress management, Wellbeing, Active listening, Help-seeking, Digital wellbeing',
 'weeks' => [
  $W('Understanding stress', 'What stress is, what it does and what helps.', [
    $R('Everyone has a mind to look after', 10, "Mental health means how we think, feel and cope. Like physical health, it goes up and down. Feeling worried before an exam is normal. It becomes a concern when feelings are strong, last a long time or stop you from living your life.\n\nAsking for help is a sign of strength, and mental health problems are common and treatable.", true),
    $R('Signs and small tools', 12, "**Common signs:** trouble sleeping, constant worry, feeling hopeless, changes in appetite, withdrawing from friends, irritability, or loss of interest.\n\n**Small tools that help:**\n- Slow breathing: in for four, out for six, a few times.\n- Move: a walk or dancing lifts mood.\n- Write worries down, then pick one small step.\n- Talk to someone you trust.\n- Keep regular sleep and meals."),
    $D('What helps you', 'What do you do when you feel stressed that really helps? Share one idea others can try.'),
   ], [
    $Q('Which is true about mental health?', ['Everyone has mental health that can go up and down', 'Only a few people have it', 'It never changes', 'Talking about it is shameful'], 0, 'Mental health is part of everyone\'s life.'),
    $Q('Which is a healthy way to cope with stress?', ['Slow breathing, movement and talking to someone you trust', 'Ignoring it forever', 'Isolating yourself', 'Staying awake all night'], 0, 'Simple habits and support reduce stress.'),
    $Q('When might feelings become a concern?', ['When they are strong, long-lasting and stop daily life', 'When you laugh at a joke', 'When you are tired once', 'When you get hungry'], 0, 'Severity and duration matter.'),
  ]),
  $W('Habits that protect you', 'Sleep, connection and managing the online world.', [
    $R('Sleep, body and routine', 10, "Poor sleep makes worry louder. Aim for regular sleep and wake times, avoid screens just before bed and limit caffeine late in the day.\n\nEat regular meals, drink water and move your body. A routine gives the day structure, which helps when life feels uncertain.", true),
    $R('Connection and social media', 12, "Real connection protects mental health: friends, family, faith groups, clubs and sports.\n\nSocial media can inform and connect, but constant comparison hurts. Remember that people post highlights, not whole lives. Curate your feed, take breaks, switch off notifications at night and never share cruel messages or private images."),
    $D('Online habits', 'What is one change you could make to feel better about your time online?'),
   ], [
    $Q('Which habit helps sleep?', ['A regular sleep and wake time', 'Using the phone in bed all night', 'Drinking strong coffee late', 'Skipping meals'], 0, 'Routine supports good sleep.'),
    $Q('Why can social media comparison hurt?', ['People post highlights, not their whole lives', 'It uses too little data', 'It is always true', 'It improves sleep'], 0, 'Comparisons are often unfair.'),
    $Q('Which is a protective factor for wellbeing?', ['Supportive friends and family', 'Isolation', 'Constant arguments', 'Skipping all rest'], 0, 'Connection protects mental health.'),
  ]),
  $W('Helping others and getting help', 'Listen well, avoid stigma and know where to find support.', [
    $R('How to listen', 12, "When a friend is struggling:\n\n- Find a private moment and ask: \"How are you really doing?\"\n- Listen without interrupting or judging.\n- Say: \"Thank you for telling me. That sounds hard.\"\n- Do not promise to keep secrets if their safety is at risk.\n- Encourage them to talk to a trusted adult or professional and offer to go with them.\n\nYou are a friend, not a counsellor.", true),
    $R('When to get help', 10, "Seek help urgently if someone talks about wanting to die or hurt themselves, or if they cannot cope. Contact emergency services, a health facility, a school counsellor or a national helpline in your country, and stay with the person.\n\nFor ongoing worries, a counsellor, nurse or doctor can guide you. You do not need to wait until it is very bad."),
    $D('Reducing stigma', 'What words or attitudes make it hard to ask for help? What would make it easier?'),
   ], [
    $Q('When a friend opens up, a good first response is:', ['Listen without judging and thank them for sharing', 'Tell them to stop being dramatic', 'Laugh it off', 'Tell everyone'], 0, 'Listening and respect are the start of support.'),
    $Q('What should you do if someone talks about harming themselves?', ['Stay with them and get urgent help from a trusted adult or emergency service', 'Promise to keep it a secret', 'Ignore it', 'Argue'], 0, 'Safety comes before secrecy.'),
    $Q('Asking for help is:', ['A sign of strength', 'A weakness', 'A waste of time', 'Shameful'], 0, 'Seeking support is a sensible step.'),
  ], ['My wellbeing plan', "In 150 to 250 words write: (1) three habits you will practise for wellbeing; (2) two people or places you can turn to for support; and (3) how you would help a friend who seems overwhelmed."]),
 ],
],

// ============================== CLIMATE & ENERGY ==============================
[
 'title' => 'Solar Energy and Clean Cooking Basics', 'category' => 'Climate & Energy', 'level' => 'Beginner', 'hours' => 3, 'color' => '#f59e0b',
 'owner' => 'grace@youthconnect.test',
 'summary' => 'Understand how solar power and clean cooking work, what they cost and how young people can build businesses around them.',
 'description' => "Millions of homes and small businesses in East Africa still depend on kerosene, charcoal or unreliable grid power. Solar lights, solar home systems and clean cookstoves are changing that.\n\nThis course explains in plain language how solar systems work, how to size one for a household, and how clean cooking saves money and protects health. It is an introduction: always use a qualified technician for wiring.",
 'learn' => "Explain how a solar panel, battery and controller work together\nWork out how much power a household needs\nCompare solar options and costs, including pay-as-you-go\nExplain the health and cost benefits of clean cookstoves\nIdentify energy business opportunities for young people",
 'skills' => 'Solar basics, Energy planning, Clean cooking, Safety, Green enterprise',
 'weeks' => [
  $W('How solar works', 'From sunshine to light: panel, battery, controller and load.', [
    $R('The parts of a solar system', 12, "A basic solar home system has four parts.\n\n- **Solar panel:** turns sunlight into electricity during the day.\n- **Charge controller:** protects the battery from overcharging.\n- **Battery:** stores energy for night and cloudy days.\n- **Loads:** lights, phone chargers, radios or a TV.\n\nPanels must face the sun, be kept clean and not be shaded by trees or roofs.", true),
    $R('Watts, hours and watt-hours', 12, "**Power (watts)** tells you how fast a device uses energy. **Energy (watt-hours)** is power multiplied by hours used.\n\nExample: a 5 W LED bulb used 4 hours a day uses 20 Wh per day. Add all devices to get your daily need, then choose a panel and battery big enough, with some spare for cloudy days."),
    $D('Power at home', 'Which devices do you or your neighbours use most? Are they efficient?'),
   ], [
    $Q('What does a charge controller do?', ['Protects the battery from overcharging', 'Makes the sun brighter', 'Cleans the panel', 'Plays radio'], 0, 'It regulates charging to protect the battery.'),
    $Q('A 10 W bulb is on for 3 hours. How much energy does it use?', ['30 Wh', '3 Wh', '13 Wh', '300 Wh'], 0, 'Energy equals power times time: 10 x 3 = 30 Wh.'),
    $Q('Why does a solar system need a battery?', ['To use electricity at night and on cloudy days', 'To make panels bigger', 'To cool the house', 'Batteries are not needed'], 0, 'The battery stores energy for when the sun is not shining.'),
  ]),
  $W('Choosing and affording solar', 'Compare products, avoid poor quality and understand pay-as-you-go.', [
    $R('Quality and safety', 10, "Not all solar products are equal. Look for quality-verified products (for example the Lighting Global / VeraSol mark), a clear warranty and local after-sales support.\n\nSafety: use correct cables, do not mix old and new batteries, keep batteries dry and ventilated and have a qualified technician install larger systems.", true),
    $R('Costs and pay-as-you-go', 12, "A solar kit costs more upfront than kerosene but saves money over time. Pay-as-you-go (PAYG) lets customers pay in small mobile-money instalments until they own the system.\n\nCompare: total price, deposit, instalment size, warranty and what happens if you miss a payment. Calculate how much you currently spend on kerosene, charging and batteries to see the payback time."),
    $D('Compare the cost', 'How much does a household you know spend each month on light and phone charging? Would solar pay back?'),
   ], [
    $Q('What should you check before buying a solar product?', ['Quality mark, warranty and after-sales support', 'Only the colour', 'Only the box size', 'Nothing'], 0, 'Quality and support protect your investment.'),
    $Q('What is pay-as-you-go solar?', ['Paying in small instalments until you own the system', 'Paying with sunshine', 'Free solar forever', 'Paying only once at the end of life'], 0, 'PAYG spreads the cost over time.'),
    $Q('How do you estimate payback time?', ['Divide the price by the monthly saving', 'Count the panels', 'Multiply by the weight', 'Guess'], 0, 'Price divided by monthly saving gives months to payback.'),
  ]),
  $W('Clean cooking and opportunities', 'Cookstoves, health and green businesses.', [
    $R('Why clean cooking matters', 12, "Cooking over open fires fills homes with smoke that harms lungs, especially for women and children, and uses a lot of wood or charcoal.\n\nImproved cookstoves burn less fuel and make less smoke. Other options include LPG, biogas and electric pressure cookers where power is affordable. Saving fuel saves money, time spent collecting wood and forests.", true),
    $R('Green energy jobs for young people', 10, "Opportunities include selling and installing solar kits, repairing devices, running phone-charging services, making and selling cookstoves, collecting and recycling batteries safely and training others.\n\nStart with a licensed company's agent programme or a cooperative. Learn about safety, customer service and records before investing."),
    $D('Your energy idea', 'Which clean energy business could work in your area and what would you need to start?'),
   ], [
    $Q('What is a health benefit of improved cookstoves?', ['Less smoke inside the home', 'More smoke', 'Louder noise', 'Hotter rooms only'], 0, 'Less smoke means fewer lung problems.'),
    $Q('Which is a clean cooking option?', ['LPG or biogas where available', 'Burning plastic', 'Burning tyres', 'Using more charcoal'], 0, 'LPG and biogas burn cleaner than open fires.'),
    $Q('Before starting an energy business you should:', ['Learn about safety, customers and costs', 'Skip training', 'Ignore warranties', 'Avoid records'], 0, 'Skills and planning reduce risk.'),
  ], ['Energy plan for a household', "Choose a household or small shop you know. In 150 to 250 words: (1) list the devices and estimate their daily watt-hours; (2) recommend a solar or clean-cooking solution; and (3) estimate monthly savings and how long it would take to pay back."]),
 ],
],

// ============================== ENTREPRENEURSHIP ==============================
[
 'title' => 'Starting a Small Business: From Idea to First Customers', 'category' => 'Entrepreneurship', 'level' => 'Beginner', 'hours' => 3, 'color' => '#0f766e',
 'owner' => 'david@youthconnect.test',
 'summary' => 'Test your idea, price it, find your first customers and keep your money in order.',
 'description' => "Many young people have good ideas but not a plan to test them cheaply. This course walks you through the steps of starting small: finding a real problem, talking to customers, pricing and tracking your money.\n\nYou finish with a one-page business plan you can show a mentor, a group or a lender.",
 'learn' => "Turn a problem into a business idea and test it with customers\nPrice a product or service so you make a profit\nPlan costs, cash and simple records\nSell, promote and keep customers\nWrite a one-page business plan",
 'skills' => 'Idea testing, Pricing, Cash planning, Marketing, Business planning',
 'weeks' => [
  $W('Idea and customers', 'Start with a problem people already pay to solve.', [
    $R('Problems, not just ideas', 10, "The best businesses solve a real problem for real people. Instead of asking \"What can I sell?\", ask \"What do people around me struggle with or spend money on?\"\n\nList five problems you see. For each, who has it, how do they solve it today and would they pay for a better way?", true),
    $R('Test before you invest', 12, "Before you spend money, talk to ten possible customers.\n\n- Ask what they do now, what annoys them and what they pay.\n- Show a sample or offer a small trial.\n- See if anyone pays or pre-orders.\n\nA small test, such as selling to ten people for a week, teaches you more than a long plan. Change your idea based on what you hear."),
    $D('Your problem list', 'Share one local problem you could solve and who would pay for it.'),
   ], [
    $Q('A good business idea usually starts with:', ['A real problem people have', 'A fancy logo', 'A big loan', 'A secret'], 0, 'Real problems create real demand.'),
    $Q('What is a good first test?', ['Offer a sample to ten potential customers', 'Spend all your savings on stock', 'Rent a large shop', 'Wait a year'], 0, 'Small tests are cheap and informative.'),
    $Q('If customers do not want to pay, you should:', ['Learn why and adjust your idea', 'Ignore them', 'Raise the price', 'Blame customers'], 0, 'Feedback helps you improve.'),
  ]),
  $W('Pricing and money', 'Cover your costs, make a profit and protect your cash.', [
    $R('Pricing', 12, "Your price must cover your costs and leave profit.\n\n**Price = cost per unit + profit margin.**\n\nExample: it costs 1,200 to make a chapati meal and you want 30 percent profit: price at about 1,560, then check what customers will pay and what competitors charge.\n\nDo not forget your own time, transport, packaging and losses.", true),
    $R('Cash and records', 12, "Many businesses with sales still fail because they run out of cash.\n\n- Separate business money from personal money.\n- Write down every sale and expense daily.\n- Keep a small reserve.\n- Pay yourself a fixed small amount instead of taking cash whenever you want.\n\nAt the end of the week add up: sales minus expenses equals profit."),
    $D('Price check', 'Choose a product you could sell. What does it cost to make and what price would you charge?'),
   ], [
    $Q('Price should at least cover:', ['Costs plus a profit', 'Only the cost of the sign', 'Nothing', 'Only the time of day'], 0, 'Without covering costs plus profit, the business loses money.'),
    $Q('Why separate business and personal money?', ['You can see if the business is really making profit', 'It is a legal trick', 'It makes taxes disappear', 'It is not important'], 0, 'Mixing money hides true results.'),
    $Q('Profit equals:', ['Sales minus expenses', 'Sales plus expenses', 'Expenses only', 'Stock only'], 0, 'What remains after costs.'),
  ]),
  $W('Customers and a plan', 'Win customers, keep them and write a one-page plan.', [
    $R('Finding and keeping customers', 12, "Start where your customers already are: neighbours, markets, WhatsApp groups, schools and churches.\n\n- Make it easy to buy and pay (mobile money).\n- Deliver on time and as promised.\n- Ask for feedback and referrals.\n- Thank customers and remember them.\n\nA happy customer tells others for free.", true),
    $R('The one-page plan', 10, "Write on one page: the problem, your product, your customers, your price, your costs, how you will reach customers, how much money you need and the first three things you will do this month.\n\nShare it with a mentor or a friend and ask what is unclear. A plan is a living tool and you update it as you learn."),
    $D('Your first ten customers', 'Where will you find your first ten customers? What will you say to them?'),
   ], [
    $Q('Which action helps you keep customers?', ['Deliver on time and ask for feedback', 'Ignore complaints', 'Change the price every day', 'Disappear after selling'], 0, 'Reliability and listening build loyalty.'),
    $Q('What belongs in a one-page plan?', ['Problem, customers, price, costs and first steps', 'Only the logo', 'Only your name', 'Nothing'], 0, 'A short plan covers the key decisions.'),
    $Q('Why share your plan with a mentor?', ['To spot gaps and improve it', 'To avoid working', 'To hide it', 'It is useless'], 0, 'Outside feedback strengthens plans.'),
  ], ['My one-page business plan', "Write your one-page plan in 200 to 300 words: the problem, your product or service, your customers, price and costs, how you will reach customers, and your first three actions."]),
 ],
],

// ============================== CREATIVE & MEDIA ==============================
[
 'title' => 'Digital Storytelling with a Phone', 'category' => 'Creative & Media', 'level' => 'Beginner', 'hours' => 3, 'color' => '#be185d',
 'owner' => 'grace@youthconnect.test',
 'summary' => 'Tell stories that matter with photos, short videos and sound, using only a smartphone and free tools.',
 'description' => "A phone is a camera, a recorder and an editing suite. Young people use them to share farm tips, promote small businesses, raise awareness and tell community stories.\n\nThis course teaches how to plan a short story, capture good photos, video and sound, and share responsibly and respectfully.",
 'learn' => "Plan a short story with a beginning, middle and end\nTake clear photos and steady video with good light and sound\nEdit with free mobile apps\nGet consent and respect people's dignity\nShare and measure your story's reach",
 'skills' => 'Storytelling, Photography, Video, Editing, Media ethics',
 'weeks' => [
  $W('Find the story', 'What to say, who it is for and how to plan it.', [
    $R('What makes a story', 10, "A story has a person, a problem or goal, and a change. \"A young farmer lost half her tomatoes, then learned to dry them and now sells to a school\" is a story. \"Farming is hard\" is a statement.\n\nBefore filming, answer: who is it for, what do I want them to feel or do, and who is the main person?", true),
    $R('Plan with a storyboard', 12, "Sketch six to eight simple pictures on paper: the opening shot, the problem, the action, the result and the closing line.\n\nPlan the shots you need: wide (the place), medium (the person) and close-up (hands, faces, details). A plan saves time and battery."),
    $D('Your story idea', 'Share a short story idea in your community and who it is for.'),
   ], [
    $Q('A story usually has:', ['A person, a challenge and a change', 'Only a title', 'Only music', 'Only a hashtag'], 0, 'People and change make a story.'),
    $Q('What is a storyboard?', ['A simple plan of the shots you need', 'A type of camera', 'A video app', 'A story about boards'], 0, 'It plans your sequence before you film.'),
    $Q('Why decide your audience first?', ['It shapes what you say and how you say it', 'It makes the phone faster', 'It is not needed', 'It reduces data'], 0, 'Audience guides content and style.'),
  ]),
  $W('Capture with your phone', 'Light, framing and clean sound.', [
    $R('Photos and video basics', 12, "- **Light:** face the light, not away from it. Soft morning or evening light is flattering.\n- **Steady:** hold the phone with two hands or rest it on something. Film in landscape for most uses.\n- **Frame:** place your subject slightly off-centre and leave space; keep the background tidy.\n- **Clean the lens** with a soft cloth.\n- Record each shot for ten seconds or more so you have material to edit.", true),
    $R('Sound matters', 10, "Bad sound ruins good pictures. Record in a quiet place, bring the phone close to the speaker (about a hand's length) and avoid wind and traffic.\n\nTest first and listen with earphones. A cheap clip-on microphone helps but is not required."),
    $D('Shot of the week', 'Describe one shot you will capture this week and how you will set it up.'),
   ], [
    $Q('For better photos you should:', ['Face the light, not the sun behind the subject', 'Always use zoom', 'Cover the lens', 'Shoot in the dark'], 0, 'Light on the subject improves clarity.'),
    $Q('How do you get clearer sound?', ['Record in a quiet place close to the speaker', 'Record from far away', 'Record in heavy wind', 'Never test it'], 0, 'Distance and noise reduce quality.'),
    $Q('Why record each video shot for several seconds?', ['It gives you room to edit', 'It saves battery', 'It makes the file smaller', 'It is a rule of the law'], 0, 'Extra length helps cuts and transitions.'),
  ]),
  $W('Edit, share and do it ethically', 'Free editing apps, consent and reach.', [
    $R('Editing simply', 12, "Free apps (such as CapCut, InShot or a phone's built-in editor) let you trim clips, add titles and captions, and mix sound.\n\n- Keep it short: 60 to 90 seconds is enough for most stories.\n- Cut out the boring parts.\n- Add captions so people can watch without sound.\n- Check volume and spelling before exporting.", true),
    $R('Consent, dignity and sharing', 12, "Always ask permission before filming or posting people, and be extra careful with children. Explain where it will be shared and let them say no.\n\n- Show people with dignity, not as victims.\n- Do not share private places or sensitive information.\n- Credit music and images or use free-to-use ones.\n- After posting, look at views, comments and shares to learn what worked."),
    $D('Your ethics check', 'What would you ask permission for before sharing your story? How would you ask?'),
   ], [
    $Q('Why add captions?', ['People can follow without sound', 'To make the video longer', 'To hide the speaker', 'It is not helpful'], 0, 'Many people watch muted or cannot hear.'),
    $Q('Before filming someone you should:', ['Ask for their permission', 'Film secretly', 'Avoid telling them', 'Post first, ask later'], 0, 'Consent is essential.'),
    $Q('How can you learn what worked after sharing?', ['Look at views, comments and shares', 'Never check', 'Delete everything', 'Guess'], 0, 'Feedback shows what to repeat.'),
  ], ['My 60-second story plan', "Plan a 60-second story. In 150 to 250 words describe: (1) the person, the problem and the change; (2) your six key shots; and (3) how you will get permission and where you will share it."]),
 ],
],

// ============================== DATA SKILLS ==============================
[
 'title' => 'Data Basics: Collect, Clean and Read Data', 'category' => 'Data Skills', 'level' => 'Beginner', 'hours' => 3, 'color' => '#1d4ed8',
 'owner' => 'grace@youthconnect.test',
 'summary' => 'Collect simple data, tidy it in a spreadsheet and read charts, so you can make better decisions in farming, business or projects.',
 'description' => "Data helps farmers decide when to plant, shopkeepers decide what to stock and project teams show results. You do not need to code to start.\n\nThis course covers asking a clear question, collecting data carefully, cleaning and summarising it in a spreadsheet, and reading charts honestly.",
 'learn' => "Turn a question into data you can collect\nRecord data neatly and consistently\nClean data and calculate totals and averages\nChoose and read a simple chart\nSpot misleading charts and protect people's privacy",
 'skills' => 'Data collection, Spreadsheets, Averages, Charts, Data ethics',
 'weeks' => [
  $W('Ask and collect', 'Start with a question and collect data you can trust.', [
    $R('Start with a question', 10, "Data is only useful if it answers a question. \"Which of my three vegetables earns the most per week?\" is a good question. \"Collect everything\" is not.\n\nWrite your question, decide what you need to measure (price, quantity, date), and how often.", true),
    $R('Collecting well', 12, "- Use the same units every time (kilograms, not sometimes sacks).\n- Record date, place and who collected it.\n- Use a form or a table with fixed columns.\n- Collect from enough cases: ten records are better than two.\n- Ask permission when collecting information about people and keep it safe.\n\nMessy collection makes analysis painful later."),
    $D('Your question', 'Share a question you would like data to answer in your farm, shop or project.'),
   ], [
    $Q('A good data question is:', ['Clear and specific', 'Vague and huge', 'Impossible to measure', 'Secret'], 0, 'Clear questions guide what to collect.'),
    $Q('Why use the same units?', ['So numbers can be compared and added', 'To use more paper', 'To confuse readers', 'It does not matter'], 0, 'Consistent units avoid mistakes.'),
    $Q('Before collecting data about people you should:', ['Ask permission and protect their information', 'Share it publicly', 'Collect secretly', 'Sell it'], 0, 'Respect privacy and consent.'),
  ]),
  $W('Clean and summarise', 'Tidy a table, then total and average it in a spreadsheet.', [
    $R('Tidy tables', 12, "A tidy spreadsheet has one row per record and one column per measurement, with a clear heading at the top.\n\nClean by fixing spelling differences (\"Kampala\" and \"kampala \"), removing duplicates, filling or flagging missing values and checking impossible numbers (for example a price of 0 or a tomato weighing 500 kg).", true),
    $R('Totals and averages', 12, "In any spreadsheet:\n- `=SUM(B2:B10)` adds numbers.\n- `=AVERAGE(B2:B10)` gives the mean.\n- `=MAX` and `=MIN` find the highest and lowest.\n\nAn average hides extremes: five days of 10 sales and one of 100 gives an average of 25, which describes none of the days. Look at the whole list too."),
    $D('Messy data', 'What problems have you seen in lists or records (duplicates, different spellings, missing values)?'),
   ], [
    $Q('In a tidy table each row is:', ['One record', 'A whole month', 'A chart', 'A decoration'], 0, 'One row per record keeps analysis simple.'),
    $Q('Which formula adds the numbers in B2 to B10?', ['=SUM(B2:B10)', '=ADD(B2)', '=B2B10', '=TOTAL'], 0, 'SUM adds a range of cells.'),
    $Q('Why can an average mislead?', ['Extreme values can hide what is typical', 'It always equals the maximum', 'It cannot be calculated', 'It is always correct'], 0, 'Unusual values can distort the average.'),
  ]),
  $W('Charts and honest data', 'Choose the right chart and spot misleading ones.', [
    $R('Choosing a chart', 10, "- **Bar chart:** compare categories (sales by product).\n- **Line chart:** show change over time (rainfall by month).\n- **Pie chart:** parts of a whole, with few slices.\n\nGive every chart a clear title, labels and units. Keep it simple: one message per chart.", true),
    $R('Honest charts and privacy', 12, "Charts can mislead: an axis that does not start at zero makes small differences look huge, cherry-picked dates hide the full story and missing labels confuse readers.\n\nBe honest: show the full range, say where the data came from and avoid sharing names or private details. Anonymise data about people whenever possible."),
    $D('Spot the trick', 'Have you seen a chart that seemed to exaggerate something? What was misleading?'),
   ], [
    $Q('Which chart suits change over time?', ['Line chart', 'Pie chart with 15 slices', 'A photo', 'A table without numbers'], 0, 'Lines show trends.'),
    $Q('Why can a truncated axis mislead?', ['It makes small differences look large', 'It improves accuracy', 'It hides the title', 'It makes charts smaller'], 0, 'Axes that skip zero exaggerate differences.'),
    $Q('When sharing data about people you should:', ['Remove names and private details', 'Publish everything', 'Add phone numbers', 'Sell it'], 0, 'Anonymising protects privacy.'),
  ], ['A mini data project', "Collect at least ten records about something real (for example weekly sales, rainfall or prices). In 150 to 250 words: (1) state your question; (2) give the total and average; and (3) describe one chart you would draw and what it shows."]),
 ],
],

];
