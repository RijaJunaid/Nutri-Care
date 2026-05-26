-- Complete NutriCare Database Schema

-- Database creation
CREATE DATABASE IF NOT EXISTS NutriCare;
USE NutriCare;

-- 1. USER TABLES
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    age INT,
    gender ENUM('Male', 'Female', 'Other', 'Prefer not to say'),
    weight DECIMAL(5,2),
    height DECIMAL(5,2),
    diet_preference ENUM('Vegetarian', 'Non-Vegetarian'),
    child_mode BOOLEAN DEFAULT FALSE,
    profile_completed BOOLEAN DEFAULT FALSE,
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE user_auth (
    auth_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider ENUM('email', 'google', 'facebook', 'apple') NOT NULL,
    provider_id VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 2. HEALTH PROFILE TABLES
CREATE TABLE medical_conditions (
    condition_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_conditions (
    user_condition_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    condition_id INT NOT NULL,
    severity ENUM('Mild', 'Moderate', 'Severe'),
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (condition_id) REFERENCES medical_conditions(condition_id),
    UNIQUE KEY unique_user_condition (user_id, condition_id)
);

CREATE TABLE allergens (
    allergen_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_allergens (
    user_allergen_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    allergen_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (allergen_id) REFERENCES allergens(allergen_id),
    UNIQUE KEY unique_user_allergen (user_id, allergen_id)
);


-- 3. FOOD SYSTEM TABLES
CREATE TABLE food_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE foods (
    food_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category_id INT,
    calories DECIMAL(6,2),
    protein DECIMAL(6,2),
    carbs DECIMAL(6,2),
    fat DECIMAL(6,2),
    fiber DECIMAL(6,2),
    sodium DECIMAL(6,2),
    sugar DECIMAL(6,2),
    glycemic_index INT,
    image_url VARCHAR(255),
    is_common_allergen BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES food_categories(category_id)
);

CREATE TABLE food_recommendations (
    recommendation_id INT AUTO_INCREMENT PRIMARY KEY,
    food_id INT NOT NULL,
    condition_id INT NOT NULL,
    recommendation_type ENUM('Recommended', 'Avoid', 'Moderation') NOT NULL,
    reason TEXT NOT NULL,
    scientific_evidence TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (food_id) REFERENCES foods(food_id) ON DELETE CASCADE,
    FOREIGN KEY (condition_id) REFERENCES medical_conditions(condition_id) ON DELETE CASCADE,
    UNIQUE KEY unique_food_condition (food_id, condition_id)
);

CREATE TABLE food_swaps (
    swap_id INT AUTO_INCREMENT PRIMARY KEY,
    original_food_id INT NOT NULL,
    better_food_id INT NOT NULL,
    reason TEXT NOT NULL,
    benefit_description TEXT,
    condition_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (original_food_id) REFERENCES foods(food_id),
    FOREIGN KEY (better_food_id) REFERENCES foods(food_id),
    FOREIGN KEY (condition_id) REFERENCES medical_conditions(condition_id) ON DELETE SET NULL
);

CREATE TABLE user_favorites (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    food_id INT,
    swap_id INT,
    exercise_id INT,
    is_tried BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES foods(food_id) ON DELETE SET NULL,
    FOREIGN KEY (swap_id) REFERENCES food_swaps(swap_id) ON DELETE SET NULL,
    CHECK (food_id IS NOT NULL OR swap_id IS NOT NULL OR exercise_id IS NOT NULL)
);

-- Child Nutrition Tables 
CREATE TABLE child_nutrition_recommendations (
    recommendation_id INT AUTO_INCREMENT PRIMARY KEY,
    age_group ENUM('0-3 years', '4-8 years', '9-13 years', '14-18 years') NOT NULL,
    food_id INT NOT NULL,
    recommendation TEXT NOT NULL,
    nutritional_benefits TEXT,
    serving_suggestion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (food_id) REFERENCES foods(food_id) ON DELETE CASCADE
);


CREATE TABLE child_nutrition_avoid_foods (
    avoid_id INT AUTO_INCREMENT PRIMARY KEY,
    age_group ENUM('0-3 years', '4-8 years', '9-13 years', '14-18 years') NOT NULL,
    name VARCHAR(100) NOT NULL,
    reason TEXT NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE child_nutrition_tips (
    tip_id INT AUTO_INCREMENT PRIMARY KEY,
    age_group ENUM('0-3 years', '4-8 years', '9-13 years', '14-18 years') NOT NULL,
    tip_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE child_nutrition_descriptions (
    description_id INT AUTO_INCREMENT PRIMARY KEY,
    age_group ENUM('0-3 years', '4-8 years', '9-13 years', '14-18 years') NOT NULL,
    description_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. EXERCISE TABLES
CREATE TABLE exercise_types (
    exercise_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    intensity ENUM('Low', 'Moderate', 'High'),
    duration_minutes INT,
    calories_burned DECIMAL(6,2),
    video_url VARCHAR(255),
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE exercise_recommendations (
    exercise_recommendation_id INT AUTO_INCREMENT PRIMARY KEY,
    exercise_id INT NOT NULL,
    condition_id INT NOT NULL,
    frequency VARCHAR(50) NOT NULL,
    duration_suggestion VARCHAR(100),
    notes TEXT,
    benefits TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exercise_id) REFERENCES exercise_types(exercise_id) ON DELETE CASCADE,
    FOREIGN KEY (condition_id) REFERENCES medical_conditions(condition_id) ON DELETE CASCADE
);

-- 6. PREMIUM CONSULTATION TABLES
CREATE TABLE nutritionists (
    nutritionist_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    specialization VARCHAR(100),
    qualifications TEXT,
    experience_years INT,
    hourly_rate DECIMAL(8,2),
    bio TEXT,
    profile_picture VARCHAR(255),
    average_rating DECIMAL(3,2) DEFAULT 0.0,
    total_reviews INT DEFAULT 0,
    meeting_link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE nutritionist_specialties (
    specialty_id INT AUTO_INCREMENT PRIMARY KEY,
    nutritionist_id INT NOT NULL,
    condition_id INT NOT NULL,
    FOREIGN KEY (nutritionist_id) REFERENCES nutritionists(nutritionist_id) ON DELETE CASCADE,
    FOREIGN KEY (condition_id) REFERENCES medical_conditions(condition_id) ON DELETE CASCADE,
    UNIQUE KEY unique_nutritionist_specialty (nutritionist_id, condition_id)
);

CREATE TABLE nutritionist_availability (
    availability_id INT AUTO_INCREMENT PRIMARY KEY,
    nutritionist_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (nutritionist_id) REFERENCES nutritionists(nutritionist_id) ON DELETE CASCADE
);

CREATE TABLE nutritionist_unavailable_dates (
    unavailable_id INT AUTO_INCREMENT PRIMARY KEY,
    nutritionist_id INT NOT NULL,
    date DATE NOT NULL,
    reason VARCHAR(255),
    FOREIGN KEY (nutritionist_id) REFERENCES nutritionists(nutritionist_id) ON DELETE CASCADE,
    UNIQUE KEY unique_nutritionist_date (nutritionist_id, date)
);


CREATE TABLE consultations (
    consultation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nutritionist_id INT NOT NULL,
    scheduled_time DATETIME NOT NULL,
    duration_minutes INT DEFAULT 60,
    status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
    payment_status ENUM('Pending', 'Paid', 'Refunded') DEFAULT 'Pending',
    payment_method ENUM('Credit Card', 'Bank Transfer', 'JazzCash', 'EasyPaisa', 'Other'),
    amount DECIMAL(8,2),
    notes TEXT,
    health_documents VARCHAR(255),
    feedback TEXT,
    rating TINYINT,
    meeting_link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (nutritionist_id) REFERENCES nutritionists(nutritionist_id) ON DELETE CASCADE
);

CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    consultation_id INT NOT NULL,
    amount DECIMAL(8,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    status ENUM('Pending', 'Completed', 'Failed', 'Refunded') DEFAULT 'Pending',
    payment_details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (consultation_id) REFERENCES consultations(consultation_id) ON DELETE CASCADE
);

CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    consultation_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (consultation_id) REFERENCES consultations(consultation_id) ON DELETE CASCADE
);


-- 7. CONTACT & SUPPORT TABLES
CREATE TABLE contact_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('New', 'In Progress', 'Resolved') DEFAULT 'New',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE faqs (
    faq_id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(50),
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE website_reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);
-- 8. INITIAL DATA INSERTION

-- Medical Conditions
INSERT INTO medical_conditions (name, description) VALUES
('Diabetes', 'A metabolic disease that causes high blood sugar.'),
('Hypertension', 'A condition in which the force of the blood against the artery walls is too high.'),
('Heart Disease', 'A range of conditions that affect your heart.'),
('Obesity', 'A complex disease involving an excessive amount of body fat.'),
('High Cholesterol', 'High levels of cholesterol in the blood.'),
('Celiac Disease', 'An immune reaction to eating gluten.'),
('Lactose Intolerance', 'The inability to fully digest the sugar (lactose) in milk.');

-- Allergens
INSERT INTO allergens (name, description) VALUES
('Nuts', 'Tree nuts and peanuts'),
('Dairy', 'Milk and milk products'),
('Eggs', 'Chicken eggs and products containing eggs');

-- Food Categories
INSERT INTO food_categories (name, description) VALUES
('Grains', 'Bread, rice, pasta, and other grain products'),
('Vegetables', 'All fresh, frozen, and canned vegetables'),
('Fruits', 'All fresh, frozen, and canned fruits'),
('Dairy', 'Milk, cheese, yogurt, and other dairy products'),
('Protein Foods', 'Meat, poultry, fish, beans, eggs'),
('Fats & Oils', 'Butter, oils, and other fats'),
('Sweets', 'Sugar, candy, and other sweets'),
('Legumes', 'Beans, lentils, and peas'),
('Nuts', 'Nuts');

-- Sample Foods
INSERT INTO foods (name, category_id, calories, protein, carbs, fat, fiber, sodium, sugar, glycemic_index, description, image_url) VALUES
-- Grains
('Brown Rice', 1, 216, 5, 45, 1.8, 3.5, 10, 0.7, 55, 'Whole grain rice with more fiber than white rice', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQLQfw3seNrdYdFNJzgzIQnWUgCbA0r2D_RxQ&s'),
('Quinoa', 1, 222, 8, 39, 3.6, 5, 13, 1.6, 53, 'Protein-rich seed that is cooked and eaten like a grain','https://images.immediate.co.uk/production/volatile/sites/30/2022/05/Quinoa-707f5e8.png?resize=768,713'),
('White Rice', 1, 205, 4.3, 45, 0.4, 0.6, 1.6, 0.1, 73, 'Refined grain with less fiber than brown rice','https://i0.wp.com/www.cocoandash.com/wp-content/uploads/2021/05/IMG_0447.jpg?fit=2592%2C1728&ssl=1'),
('Whole Wheat Bread', 1, 247, 13, 41, 3.4, 6, 380, 6, 71, 'Bread made from whole wheat flour','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRhvnmSPfI3Ch2ZqQsq_4f-sXkJGuOhmWe4YQ&s'),

-- Vegetables
('Broccoli', 2, 55, 3.7, 11, 0.6, 2.6, 33, 2.2, 15, 'Nutrient-rich green vegetable','https://snaped.fns.usda.gov/sites/default/files/styles/crop_ratio_7_5/public/seasonal-produce/2018-05/broccoli.jpg.webp?itok=9hD8BBER'),
('Spinach', 2, 23, 2.9, 3.6, 0.4, 2.2, 79, 0.4, 15, 'Leafy green vegetable high in iron','https://www.thespruceeats.com/thmb/Wpdr8OgU89mQDImdVsH96i_-dd4=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/what-is-spinach-4783497-hero-07-4a4e988cb48b4973a258d1cc44909780.jpg'),

-- Protein Foods
('Salmon', 5, 208, 20, 0, 13, 0, 59, 0, 0, 'Fatty fish rich in omega-3 fatty acids','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTuFd7zlYdssGFg2XtBEJGJIiJ19plfD64xXA&s'),
('Chicken Breast', 5, 165, 31, 0, 3.6, 0, 74, 0, 0, 'Lean protein source','https://downshiftology.com/wp-content/uploads/2023/01/How-To-Make-Air-Fryer-Chicken-5.jpg'),
('Almonds', 9, 579, 21, 22, 50, 12.5, 1, 4.4, 0, 'Nutritious tree nuts high in healthy fats','https://i0.wp.com/post.healthline.com/wp-content/uploads/2023/02/Almonds-Table-Bowl-1296x728-Header.jpg?w=1155&h=1528'),

-- Dairy
('Greek Yogurt', 4, 59, 10, 3.6, 0.4, 0, 36, 3.2, 0, 'Thick, protein-rich yogurt with probiotics','https://www.liveeatlearn.com/wp-content/uploads/2024/08/how-to-make-homemade-greek-yogurt-25.jpg'),
('Oats', 1, 389, 16.9, 66.3, 6.9, 10.6, 2, 0, 55, 'Whole grain rich in soluble fiber','https://media.post.rvohealth.io/wp-content/uploads/2020/09/oats-1200x628-facebook-1200x628.jpg'),
('Blueberries', 3, 57, 0.7, 14.5, 0.3, 2.4, 1, 10, 53, 'Antioxidant-rich berries','https://foodmarble.com/more/wp-content/uploads/2021/09/joanna-kosinska-4qujjbj3srs-unsplash-scaled.jpg'),
('Walnuts', 9, 654, 15.2, 13.7, 65.2, 6.7, 2, 2.6, 15, 'Omega-3 rich nuts','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRNKYGude3t4Rfvmc-grYtiy-TeAqEaJ07wug&s'),
('Lentils', 8, 116, 9, 20, 0.4, 8, 2, 1.8, 32, 'Plant-based protein and fiber source','https://lentillovingfamily.com/wp-content/uploads/2024/05/lentil-types-1.jpg'),
('Avocado', 3, 160, 2, 8.5, 14.7, 6.7, 7, 0.7, 10, 'Healthy fat fruit','https://nutritionsource.hsph.harvard.edu/wp-content/uploads/2022/04/pexels-antonio-filigno-8538296-1024x657.jpg'),
('Sweet Potato', 2, 86, 1.6, 20.1, 0.1, 3, 55, 4.2, 63, 'Nutrient-dense root vegetable','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT0mmY2E1iC5l8L4RgJrXnTo27dl2qnwmq_VQ&s'),
('Chia Seeds', 8, 486, 16.5, 42.1, 30.7, 34.4, 16, 0, 30, 'High fiber superfood','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSxRVT_xhMOz9mNV8eixIcZiOYGNnXHOCjQJQ&s'),
('Milk Chocolate', 7, 535, 7.6, 59, 30, 3.4, 79, 54, 45, 'Sweet chocolate containing milk solids and sugar','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRQLt0xLjMnjIhXfj_T-uWVDfOfVYzn1u6Oqw&s'),
('Dark Chocolate', 7, 598, 7.8, 46, 43, 11, 20, 24, 23, 'Chocolate with higher cocoa content and less sugar','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRP5aw27nP2ZO6EjKqruFN_74-pT5OlHZhk9A&s'),
('Pasta', 1, 131, 5, 25, 1, 1.2, 1, 0.5, 50, 'Refined wheat pasta with lower fiber content than whole wheat','https://www.allrecipes.com/thmb/IrY572TXic4UXXVn8EetsarI3S0=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/AR-269500-creamy-garlic-pasta-Beauties-4x3-f404628aad2a435a9985b2cf764209b5.jpg'),
('Whole Wheat Pasta', 1, 124, 5.5, 25, 0.6, 3.9, 1, 0.4, 42, 'Pasta made from whole wheat flour with more fiber','https://static01.nyt.com/images/2013/04/24/science/26recipehealth/26recipehealth-superJumbo.jpg'),
('White Flour', 1, 364, 10, 76, 1, 2.7, 2, 0.3, 85, 'Refined wheat flour with lower fiber content','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRMUoraVFn3a7QhC40h7WfnsNP-HP4nQ0bJdQ&s'),
('Whole Wheat Flour', 1, 340, 13, 72, 2.5, 10.7, 2, 0.4, 74, 'Flour made from whole wheat with more fiber and nutrients','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRynrjvU5CdhWs-GpOIkWNwQxrjik-3PLtQYQ&s'),
('Fried Chicken', 5, 300, 25, 15, 15, 0.5, 450, 0, 0, 'Chicken prepared with breading and frying','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTpzXpzSU6VdWVZoPO6oQoJPn7E-lRKvdL_iQ&s'),
('Grilled Chicken', 5, 165, 31, 0, 3.6, 0, 74, 0, 0, 'Chicken prepared without added fats','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTJp3NLE0wdhqlsbL1HvAXBwlrc8kguL7qSyA&s'),
('Potato Chips', 2, 536, 7, 53, 35, 4.8, 390, 0.4, 56, 'Fried potato slices high in fat and sodium','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRxm0m2USR8pwkQUMQu3lNGn-VTB_knAkIyUA&s'),
('Roasted Potatoes', 2, 93, 2, 21, 0.1, 2.2, 10, 1.2, 58, 'Healthier prepared potato option','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ9JOdCjT8IruXsztneeD2DsqLXLoXw8GNA_w&s');
-- Food Recommendations
INSERT INTO food_recommendations (food_id, condition_id, recommendation_type, reason, scientific_evidence) VALUES
-- Diabetes
(1, 1, 'Recommended', 'Low glycemic index helps control blood sugar levels', 'Studies show low-GI foods help manage blood glucose levels.'),
(2, 1, 'Recommended', 'High in protein and fiber which helps with blood sugar control', 'Quinoa has a low glycemic index and high protein content.'),
(3, 1, 'Avoid', 'High glycemic index can cause blood sugar spikes', 'White rice has a high glycemic index compared to whole grains.'),

-- Heart Disease
(7, 3, 'Recommended', 'Omega-3 fatty acids help reduce inflammation and support heart health', 'EPA and DHA in salmon reduce cardiovascular risk factors.'),
(9, 3, 'Recommended', 'Healthy monounsaturated fats support heart health', 'Almonds contain heart-healthy fats and vitamin E.'),

-- High Cholesterol
(7, 5, 'Recommended', 'Omega-3s help lower triglycerides', 'Fish oil supplementation reduces triglyceride levels.'),
(9, 5, 'Recommended', 'Monounsaturated fats can help lower LDL cholesterol', 'Nuts have been shown to improve lipid profiles.'),

(4, 1, 'Avoid', 'Refined grains can cause blood sugar spikes', 'White bread has a high glycemic index and low fiber content'),
(10, 1, 'Avoid', 'Sweetened yogurt contains added sugars', 'Added sugars can rapidly increase blood glucose levels'),

-- Hypertension - Avoid
(10, 2, 'Avoid', 'Some yogurts can be high in sodium', 'High sodium intake is linked to increased blood pressure'),
(4, 2, 'Avoid', 'Processed bread often contains high sodium', 'Commercial bread is a major source of dietary sodium'),

-- Hypertension - Moderate
(8, 2, 'Moderation', 'Lean protein is good but avoid adding salt', 'Chicken is a healthy protein when prepared without added salt'),
(9, 2, 'Moderation', 'Nuts are healthy but high in calories', 'Almonds contain healthy fats but should be consumed in moderation'),

-- Heart Disease - Avoid
(3, 3, 'Avoid', 'Refined grains may increase heart disease risk', 'White rice is associated with higher cardiovascular risk in some studies'),
(4, 3, 'Avoid', 'Processed grains may contribute to heart disease', 'Refined grains lack beneficial nutrients found in whole grains'),

-- Obesity - Avoid
(3, 4, 'Avoid', 'Refined carbohydrates can contribute to weight gain', 'White rice is calorie-dense with low satiety'),
(4, 4, 'Avoid', 'Processed bread is often calorie-dense', 'White bread provides calories without promoting fullness'),

-- High Cholesterol - Avoid
(10, 5, 'Avoid', 'Full-fat dairy products can contain saturated fats', 'Some yogurts are high in saturated fats which may raise LDL'),
(4, 5, 'Avoid', 'Processed grains often contain unhealthy fats', 'Commercial bread may contain trans fats'),

-- Celiac Disease - Avoid (gluten-containing foods)
(1, 6, 'Avoid', 'Brown rice is safe but check for cross-contamination', 'Rice is naturally gluten-free but may be processed with wheat'),
(3, 6, 'Avoid', 'White rice is safe but check for cross-contamination', 'Pure rice is gluten-free but may be contaminated during processing'),
(4, 6, 'Avoid', 'Wheat bread contains gluten', 'Wheat products must be avoided in celiac disease'),

-- Lactose Intolerance - Avoid
(10, 7, 'Avoid', 'Yogurt contains lactose', 'Dairy products typically contain lactose unless specially processed'),

(5, 1, 'Recommended', 'Low-carb, high-fiber vegetable helps regulate blood sugar', 'Broccoli has a very low glycemic index and is rich in chromium which helps with insulin sensitivity'),
(6, 1, 'Recommended', 'Leafy greens have minimal impact on blood glucose', 'Spinach is extremely low in digestible carbs and high in magnesium which benefits diabetes management'),

-- Hypertension Recommended Foods
(5, 2, 'Recommended', 'Rich in potassium which helps lower blood pressure', 'Broccoli contains compounds that help blood vessel relaxation'),
(6, 2, 'Recommended', 'High in potassium and nitrates that help reduce blood pressure', 'Studies show spinach can help manage hypertension'),
(7, 2, 'Recommended', 'Omega-3 fatty acids help reduce blood pressure', 'EPA and DHA in salmon improve endothelial function'),

-- Heart Disease Recommended Foods
(5, 3, 'Recommended', 'Contains sulforaphane which may prevent blood vessel damage', 'Broccoli has anti-inflammatory properties beneficial for heart health'),
(6, 3, 'Recommended', 'Rich in dietary nitrates that support heart health', 'Spinach helps improve arterial stiffness'),
(10, 3, 'Recommended', 'Probiotics in yogurt support heart health', 'Fermented dairy associated with reduced cardiovascular risk'),

-- Obesity Recommended Foods
(5, 4, 'Recommended', 'Low-calorie, high-volume food promotes satiety', 'Broccoli has only 55 calories per cup but high fiber content'),
(6, 4, 'Recommended', 'Nutrient-dense with very few calories', 'Spinach provides essential nutrients without excess calories'),
(7, 4, 'Recommended', 'High-protein fish helps maintain muscle during weight loss', 'Salmon promotes satiety and preserves lean body mass'),

-- High Cholesterol Recommended Foods
(5, 5, 'Recommended', 'Soluble fiber helps reduce LDL cholesterol', 'Broccoli contains fiber that binds to bile acids'),
(6, 5, 'Recommended', 'Contains lutein which prevents cholesterol oxidation', 'Spinach phytochemicals support lipid metabolism'),

-- Celiac Disease Recommended Foods
(5, 6, 'Recommended', 'Naturally gluten-free and nutrient-dense', 'Broccoli is safe for gluten-free diets and highly nutritious'),
(6, 6, 'Recommended', 'Gluten-free leafy green packed with nutrients', 'Spinach provides iron often lacking in gluten-free diets'),
(7, 6, 'Recommended', 'Excellent gluten-free protein source', 'Salmon provides essential fatty acids missing in some GF diets'),

-- Lactose Intolerance Recommended Foods
(5, 7, 'Recommended', 'Dairy-free calcium-rich vegetable', 'Broccoli provides calcium without lactose'),
(6, 7, 'Recommended', 'Excellent source of lactose-free nutrients', 'Spinach provides calcium, magnesium and iron'),
(7, 7, 'Recommended', 'Provides vitamin D without dairy', 'Salmon is a natural source of vitamin D'),
(9, 7, 'Recommended', 'Dairy-free source of calcium', 'Almonds provide calcium without lactose'),
(11, 1, 'Recommended', 'Beta-glucan fiber helps control blood sugar', 'Oats significantly improve glycemic control'),
(11, 2, 'Recommended', 'Soluble fiber helps lower blood pressure', 'Oat consumption associated with reduced hypertension risk'),
(11, 5, 'Recommended', 'Reduces LDL cholesterol absorption', 'FDA-approved health claim for oats and heart health'),

-- Blueberries
(12, 1, 'Recommended', 'Anthocyanins improve insulin sensitivity', 'Berries have low glycemic impact and benefits for diabetes'),
(12, 3, 'Recommended', 'Antioxidants protect blood vessels', 'Blueberries improve endothelial function'),
(12, 4, 'Recommended', 'Low-calorie, high-nutrient snack', 'Berries satisfy sweet cravings without excess calories'),

-- Walnuts
(13, 3, 'Recommended', 'Plant-based omega-3s support heart health', 'Walnuts improve endothelial function and reduce inflammation'),
(13, 5, 'Recommended', 'Healthy fats improve lipid profile', 'Regular walnut consumption lowers LDL cholesterol'),

-- Lentils
(14, 1, 'Recommended', 'Slow-digesting carbs prevent blood sugar spikes', 'Lentils have very low glycemic index'),
(14, 4, 'Recommended', 'High fiber promotes satiety', 'Legumes are among the most satiating foods'),

-- Avocado
(15, 3, 'Recommended', 'Monounsaturated fats support heart health', 'Avocados improve lipid profiles and antioxidant status'),
(15, 5, 'Recommended', 'Healthy fats improve cholesterol ratios', 'Avocado consumption increases HDL while lowering LDL'),

-- Sweet Potato
(16, 1, 'Moderation', 'Fiber-rich carb source (consume in moderation)', 'Despite higher GI, fiber content moderates blood sugar impact'),
(16, 4, 'Recommended', 'Nutrient-dense, satisfying carbohydrate', 'Provides vitamin A and fiber for weight management'),

-- Chia Seeds
(17, 1, 'Recommended', 'Extremely high fiber slows glucose absorption', 'Chia seeds can help stabilize blood sugar levels'),
(17, 2, 'Recommended', 'High potassium and magnesium content', 'Chia seeds provide blood pressure-lowering minerals');


-- Food Swaps
INSERT INTO food_swaps (original_food_id, better_food_id, reason, benefit_description, condition_id) VALUES
(3, 1, 'Brown rice has more fiber and nutrients than white rice', 'Higher fiber content helps with blood sugar control and digestion', 1),
(4, 2, 'Quinoa is a complete protein and has more nutrients than white bread', 'Provides all essential amino acids and more vitamins/minerals', NULL),
(8, 7, 'Salmon provides healthy omega-3s compared to chicken', 'Omega-3 fatty acids support heart and brain health', 3),
(18, 19, 'Dark chocolate has less sugar and more antioxidants', 'Satisfies sweet cravings with more health benefits', 4),
(20, 21, 'Whole wheat pasta has more fiber and lower GI', 'Slower digestion helps prevent blood sugar spikes', 1),
(22, 23, 'Whole wheat flour has more fiber and nutrients', 'Slower glucose absorption helps blood sugar control', 1),
(24, 25, 'Grilling avoids unhealthy breading and frying oils', 'Reduces saturated and trans fat intake', 3),
(26, 27, 'Roasting avoids unhealthy fats from frying', 'Reduces calorie density and unhealthy fats', 1);
-- Nutritionists (matching your premium.php)
INSERT INTO nutritionists (name, email, password, specialization, qualifications, experience_years, hourly_rate, bio, average_rating, total_reviews, meeting_link) VALUES
('Dr. Aliya Hassan', 'aliya@nutricare.com', '$2y$10$hashedpassword', 'Diabetes & Weight Management', 'PhD in Nutrition, RD', 15, 3500, '10+ years experience helping patients manage blood sugar through nutrition.', 4.5, 142, 'https://meet.nutricare.com/aliya-hassan'),
('Dr. Abdullah Khan', 'abdullah@nutricare.com', '$2y$10$hashedpassword', 'Pediatric Nutrition', 'MS in Nutrition, RD', 8, 4000, 'Specializes in child nutrition from infancy to adolescence.', 5.0, 89, 'https://meet.nutricare.com/abdullah-khan'),
('Dr. Hadiyah Malik', 'hadiyah@nutricare.com', '$2y$10$hashedpassword', 'Sports Nutrition', 'MS in Exercise Science, RD', 10, 4500, 'Helps athletes optimize performance through tailored nutrition plans.', 4.0, 67, 'https://meet.nutricare.com/hadiyah-malik'),
('Dr. Ali Rehman', 'ali@nutricare.com', '$2y$10$hashedpassword', 'Gut Health & Digestion', 'MD in Gastroenterology, RD', 12, 3800, 'Specializes in digestive disorders and gut microbiome optimization.', 5.0, 112, 'https://meet.nutricare.com/ali-rehman'),
('Dr. Eman Javed', 'eman@nutricare.com', '$2y$10$hashedpassword', 'Plant-Based Nutrition', 'MS in Nutrition, RD', 7, 3200, 'Expert in vegetarian and vegan nutrition planning.', 4.0, 76, 'https://meet.nutricare.com/eman-javed'),
('Dr. Harris Ali', 'harris@nutricare.com', '$2y$10$hashedpassword', 'Geriatric Nutrition', 'PhD in Gerontology, RD', 20, 4200, 'Specializes in nutritional needs for older adults and seniors.', 4.5, 93, 'https://meet.nutricare.com/harris-ali');

-- Nutritionist Specialties
INSERT INTO nutritionist_specialties (nutritionist_id, condition_id) VALUES
(1, 1), (1, 4), -- Dr. Aliya Hassan: Diabetes & Obesity
(2, 1), (2, 3), -- Dr. Abdullah Khan: Diabetes & Heart Disease
(3, 4),         -- Dr. Hadiyah Malik: Obesity
(4, 1), (4, 2), (4, 3), -- Dr. Ali Rehman: Diabetes, Hypertension, Heart Disease
(5, 1), (5, 4), -- Dr. Eman Javed: Diabetes & Obesity
(6, 2), (6, 3); -- Dr. Harris Ali: Hypertension & Heart Disease

-- Nutritionist Availability
INSERT INTO nutritionist_availability (nutritionist_id, day_of_week, start_time, end_time) VALUES
-- Dr. Aliya Hassan
(1, 'Monday', '09:00:00', '17:00:00'),
(1, 'Wednesday', '09:00:00', '17:00:00'),
(1, 'Friday', '09:00:00', '17:00:00'),

-- Dr. Abdullah Khan
(2, 'Tuesday', '08:00:00', '16:00:00'),
(2, 'Thursday', '08:00:00', '16:00:00'),
(2, 'Saturday', '10:00:00', '14:00:00'),

-- Dr. Hadiyah Malik
(3, 'Monday', '10:00:00', '18:00:00'),
(3, 'Wednesday', '10:00:00', '18:00:00'),
(3, 'Friday', '10:00:00', '18:00:00'),

-- Dr. Ali Rehman
(4, 'Tuesday', '09:30:00', '17:30:00'),
(4, 'Thursday', '09:30:00', '17:30:00'),
(4, 'Saturday', '10:00:00', '14:00:00'),

-- Dr. Eman Javed
(5, 'Monday', '10:00:00', '16:00:00'),
(5, 'Wednesday', '10:00:00', '16:00:00'),
(5, 'Friday', '10:00:00', '16:00:00'),

-- Dr. Harris Ali
(6, 'Tuesday', '08:30:00', '16:30:00'),
(6, 'Thursday', '08:30:00', '16:30:00'),
(6, 'Saturday', '09:00:00', '13:00:00');

-- Nutritionist Unavailable Dates
INSERT INTO nutritionist_unavailable_dates (nutritionist_id, date, reason) VALUES
(1, '2023-07-15', 'Conference'),
(1, '2023-07-20', 'Vacation'),
(2, '2023-07-18', 'Training'),
(2, '2023-07-19', 'Training'),
(3, '2023-07-17', 'Personal'),
(3, '2023-07-21', 'Conference'),
(4, '2023-07-16', 'Holiday'),
(4, '2023-07-23', 'Holiday'),
(5, '2023-07-14', 'Workshop'),
(5, '2023-07-24', 'Workshop'),
(6, '2023-07-13', 'Medical Leave'),
(6, '2023-07-25', 'Medical Leave');

-- Exercise Types
INSERT INTO exercise_types (name, description, intensity, duration_minutes, calories_burned, video_url) VALUES
('Brisk Walking', 'Walking at a pace that raises your heart rate', 'Moderate', 30, 150, 'https://www.youtube.com/watch?v=wQrV75N2BrI'),
('Yoga', 'Gentle stretching and breathing exercises', 'Low', 45, 180, 'https://www.youtube.com/watch?v=kqmut7-RARw'),
('Swimming', 'Full-body low-impact exercise', 'Moderate', 30, 250, 'https://www.youtube.com/watch?v=Rr_CnIfr5u8'),
('Cycling', 'Low-impact cardio exercise', 'Moderate', 30, 300, 'https://m.youtube.com/watch?v=ZiGE3-L4vyg&t=2m23s'),
('Resistance Training', 'Strength exercises using body weight or equipment', 'High', 45, 350, 'https://www.youtube.com/watch?v=8YhyqGJZyKs&pp=0gcJCdgAo7VqN5tD');

-- Exercise Recommendations
INSERT INTO exercise_recommendations (exercise_id, condition_id, frequency, duration_suggestion, notes, benefits) VALUES
-- Diabetes
(1, 1, '5 times per week', '30-60 minutes', 'Monitor blood sugar before and after exercise', 'Helps improve insulin sensitivity'),
(2, 1, '3 times per week', '45-60 minutes', 'Focus on stress-reducing poses', 'Reduces stress which can affect blood sugar'),

-- Heart Disease
(1, 3, 'Daily', '30 minutes minimum', 'Maintain moderate pace', 'Helps lower blood pressure and improve circulation'),
(3, 3, '3 times per week', '30 minutes', 'Use comfortable swimming style', 'Improves cardiovascular health without joint stress'),

-- Obesity
(4, 4, '5 times per week', '45 minutes', 'Start with flat terrain', 'Effective for weight loss when combined with diet'),
(5, 4, '3 times per week', '30-45 minutes', 'Focus on full-body exercises', 'Builds muscle which increases metabolic rate');

-- FAQs
INSERT INTO faqs (question, answer, category, is_featured) VALUES
('How do I get started with NutriCare?', 'Simply create an account, complete your profile, and start exploring food recommendations based on your health needs.', 'General', TRUE),
('Is NutriCare suitable for children?', 'Yes! Enable Child Nutrition Mode in your profile to get age-appropriate recommendations for your child.', 'Features', TRUE),
('How often are the food recommendations updated?', 'Our database is continuously updated with the latest nutrition research and guidelines.', 'Food', FALSE),
('Can I consult with a nutritionist through NutriCare?', 'Yes, we offer premium consultations with certified nutritionists. Book a session through the Premium Consult page.', 'Premium', TRUE),
('What payment methods do you accept for consultations?', 'We accept credit cards, bank transfers, JazzCash, and EasyPaisa for premium consultations.', 'Premium', TRUE),
('How do food swaps help my health?', 'Food swaps suggest healthier alternatives that are better suited to your medical conditions, often with more nutrients and fewer negative effects.', 'Food', TRUE);




-- Child Nutrition Recommendations
INSERT INTO child_nutrition_recommendations (age_group, food_id, recommendation, nutritional_benefits, serving_suggestion) VALUES
-- 0-3 years
('0-3 years', 15, 'Excellent first food, rich in healthy fats for brain development', 'High in monounsaturated fats, folate, vitamin E, and potassium', 'Mash or serve in thin slices for older infants'),
('0-3 years', 16, 'Great source of vitamin A and fiber', 'Rich in beta-carotene, vitamin C, and potassium', 'Cook until soft and mash or puree for young infants'),
('0-3 years', 10, 'Full-fat plain yogurt provides calcium and probiotics', 'Good source of protein, calcium, and beneficial bacteria for gut health', 'Start with small amounts (1-2 tbsp) mixed with fruit purees'),
('0-3 years', 14, 'Excellent plant-based protein and iron source', 'High in iron, folate, and fiber important for growth', 'Cook until very soft and puree or mash thoroughly'),

-- 4-8 years
('4-8 years', 1, 'Whole grain provides sustained energy for active kids', 'Rich in fiber, B vitamins, and minerals like magnesium', '1/2 to 1 cup cooked, served with vegetables and protein'),
('4-8 years', 7, 'Important for brain development and omega-3s', 'Excellent source of protein, omega-3 fatty acids, and vitamin D', '2-3 oz servings, 2-3 times per week'),
('4-8 years', 5, 'Crucial for vitamins, minerals, and fiber', 'High in vitamin C, K, folate, and antioxidants', '1/2 to 1 cup raw or cooked daily'),
('4-8 years', 9, 'Healthy snack that provides good fats and protein', 'Rich in vitamin E, magnesium, and plant-based protein', '1 oz (about 23 almonds) as a snack'),

-- 9-13 years
('9-13 years', 2, 'Complete protein source for growing bodies', 'Contains all essential amino acids and is high in fiber', '1/2 to 1 cup cooked, great in salads or as side dish'),
('9-13 years', 8, 'Lean protein supports muscle growth', 'Excellent source of protein, niacin, and selenium', '3-4 oz servings, grilled or baked'),
('9-13 years', 11, 'Provides sustained energy and fiber', 'Rich in beta-glucan fiber that supports heart health', '1/2 to 1 cup cooked, great for breakfast'),
('9-13 years', 12, 'Antioxidant-rich fruit for immune support', 'High in vitamin C, K, and antioxidants', '1/2 to 1 cup fresh or frozen'),

-- 14-18 years
('14-18 years', 7, 'Supports brain development in teenagers', 'Rich in omega-3s important for cognitive function', '4-6 oz servings, 2-3 times per week'),
('14-18 years', 13, 'Healthy fats support hormone development', 'Excellent source of plant-based omega-3s and antioxidants', '1 oz (about 14 halves) as a snack'),
('14-18 years', 17, 'Great for energy and essential minerals', 'High in fiber, omega-3s, calcium, and magnesium', '1-2 tbsp daily in yogurt or smoothies'),
('14-18 years', 6, 'Iron-rich for growth spurts and menstruation', 'Excellent source of iron, folate, and vitamin K', '1-2 cups raw or 1/2 cup cooked daily');


-- Child Nutrition Avoid Foods
INSERT INTO child_nutrition_avoid_foods (age_group, name, reason, image_url) VALUES
('0-3 years', 'Honey', 'Risk of infant botulism', 'https://images.immediate.co.uk/production/volatile/sites/30/2024/03/Honey440-bb52330.jpg?quality=90&resize=440,400'),
('0-3 years', 'Whole Nuts', 'Choking hazard', 'https://www.ofi.com/content/dam/olamofi/products-and-ingredients/nuts/nuts-images-webp/nuts-whole-roasted.webp'),
('0-3 years', 'Cow''s Milk (as main drink)', 'Not suitable before 12 months', 'https://images.unsplash.com/photo-1550583724-b2692b85b150?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80'),

('4-8 years', 'Sugary Cereals', 'High in added sugars', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSTsYpFPEbEr3BQAPBklCErXL6_K4frH_Lg6w&s'),
('4-8 years', 'Processed Meats', 'High in sodium and preservatives', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRaOusyyWFAMxOtPOKy2mT_2zeN2ql0Ai8t3Q&s'),
('4-8 years', 'Soda', 'Empty calories and sugar', 'https://www.fodors.com/wp-content/uploads/2019/03/HERO_Worlds_Best_Soda_Bundaberg_shutterstock_679079920.jpg'),

('9-13 years', 'Energy Drinks', 'High caffeine and sugar content', 'https://www.tastingtable.com/img/gallery/15-energy-drink-brands-that-arent-red-bull-ranked/intro-1729784757.jpg'),
('9-13 years', 'Fast Food', 'High in unhealthy fats and sodium', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTILEztUySn0QQnePy8GVA_7IsbqHzlUQYgDw&s'),
('9-13 years', 'Candy', 'Excessive sugar with no nutritional value', 'https://abeautifulmess.com/wp-content/uploads/2024/06/Candy-Salad-.jpg'),

('14-18 years', 'Alcohol', 'Harmful to developing brains', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTILXhelRoK5Pl4VQoUS_BSN1tQPKrxwIbmmA&s'),
('14-18 years', 'Diet Pills', 'Unsafe and ineffective', 'https://www.honorhealth.com/sites/default/files/styles/large/public/diet-pills-2-things-to-know.jpg?itok=dPUnCv8c'),
('14-18 years', 'Excessive Caffeine', 'Can affect sleep and growth', 'https://images.immediate.co.uk/production/volatile/sites/30/2020/08/flat-white-3402c4f.jpg');

-- Child Nutrition Tips
INSERT INTO child_nutrition_tips (age_group, tip_text) VALUES
('0-3 years', 'Exclusive breastfeeding recommended for first 6 months'),
('0-3 years', 'Introduce iron-rich foods at 6 months (pureed meats, iron-fortified cereals)'),
('0-3 years', 'Introduce one new food at a time, waiting 3-5 days to check for allergies'),
('0-3 years', 'No added salt or sugar in baby foods'),
('0-3 years', 'Ensure adequate vitamin D supplementation (400 IU/day)'),

('4-8 years', 'Encourage a variety of fruits and vegetables (aim for 5 servings daily)'),
('4-8 years', 'Include protein sources at each meal (eggs, lean meats, beans, dairy)'),
('4-8 years', 'Limit juice to 4-6 oz per day and encourage water instead'),
('4-8 years', 'Establish regular meal and snack times'),
('4-8 years', 'Involve children in food preparation to encourage healthy eating'),

('9-13 years', 'Increase calcium intake for bone growth (dairy, fortified alternatives)'),
('9-13 years', 'Include iron-rich foods, especially for menstruating girls'),
('9-13 years', 'Encourage breakfast to support concentration at school'),
('9-13 years', 'Teach portion sizes to prevent overeating'),
('9-13 years', 'Limit screen time during meals to promote mindful eating'),

('14-18 years', 'Support increased calorie needs with nutrient-dense foods'),
('14-18 years', 'Encourage regular meals to support growth and energy needs'),
('14-18 years', 'Discuss healthy ways to manage weight (avoid fad diets)'),
('14-18 years', 'Promote hydration with water instead of sugary drinks'),
('14-18 years', 'Teach basic cooking skills for independence');

-- Child Nutrition Descriptions
INSERT INTO child_nutrition_descriptions (age_group, description_text) VALUES
('0-3 years', 'Proper nutrition during the first 3 years is crucial for growth and development. Here are recommended foods and important nutritional guidelines for your little one.'),
('4-8 years', 'Preschool and early school years are a time of rapid growth and development. These recommendations will help support your childs nutritional needs.'),
('9-13 years', 'Pre-teen years bring increased nutritional needs. These foods will support growth, development, and school performance.'),
('14-18 years', 'Teenagers have high nutritional needs to support growth spurts and development. These recommendations provide the foundation for healthy adulthood.');
