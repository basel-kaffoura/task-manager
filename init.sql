-- Create tasks table
CREATE TABLE IF NOT EXISTS tasks
(
    id
    SERIAL
    PRIMARY
    KEY,
    title
    VARCHAR
(
    255
) NOT NULL,
    description TEXT,
    completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
                             );

-- Insert sample data
INSERT INTO tasks (title, description, completed, created_at)
VALUES ('Buy groceries', 'Milk, Bread, Eggs', false, '2025-04-04T14:00:00Z'),
       ('Pay bills', 'Electricity, Water, Internet', false, '2025-04-04T15:30:00Z');