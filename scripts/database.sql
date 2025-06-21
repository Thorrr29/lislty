-- Create database
CREATE DATABASE IF NOT EXISTS todo_list_db;
USE todo_list_db;

-- Table for user roles
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_role VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default roles
INSERT INTO roles (nama_role) VALUES ('admin'), ('user');

-- Table for users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role_id INT DEFAULT 2,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Table for task categories
CREATE TABLE kategori (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    warna VARCHAR(7) DEFAULT '#007bff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for task priorities
CREATE TABLE prioritas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_prioritas VARCHAR(50) NOT NULL,
    level_prioritas INT NOT NULL,
    warna VARCHAR(7) DEFAULT '#28a745',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for task status
CREATE TABLE status_tugas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_status VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    warna VARCHAR(7) DEFAULT '#6c757d',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for tasks
CREATE TABLE tugas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    kategori_id INT,
    prioritas_id INT,
    status_id INT DEFAULT 1,
    tanggal_mulai DATE,
    tanggal_jatuh_tempo DATE,
    tanggal_selesai DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL,
    FOREIGN KEY (prioritas_id) REFERENCES prioritas(id) ON DELETE SET NULL,
    FOREIGN KEY (status_id) REFERENCES status_tugas(id) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, nama_lengkap, role_id) 
VALUES ('admin', 'admin@todolist.com', '$2y$10$mmMljWbK6SfebFAMkJFleO58jLwexN1rGtBgzHks7jEX7PixgpqDO', 'Administrator', 1);

-- Insert default categories
INSERT INTO kategori (nama_kategori, deskripsi, warna) VALUES
('Pekerjaan', 'Tugas-tugas yang berkaitan dengan pekerjaan', '#007bff'),
('Pribadi', 'Tugas-tugas pribadi', '#28a745'),
('Keluarga', 'Tugas-tugas keluarga', '#ffc107'),
('Pendidikan', 'Tugas-tugas pendidikan dan pembelajaran', '#17a2b8');

-- Insert default priorities
INSERT INTO prioritas (nama_prioritas, level_prioritas, warna) VALUES
('Rendah', 1, '#28a745'),
('Sedang', 2, '#ffc107'),
('Tinggi', 3, '#fd7e14'),
('Sangat Tinggi', 4, '#dc3545');

-- Insert default status
INSERT INTO status_tugas (nama_status, deskripsi, warna) VALUES
('Belum Dimulai', 'Tugas belum dimulai', '#6c757d'),
('Sedang Dikerjakan', 'Tugas sedang dalam proses', '#007bff'),
('Selesai', 'Tugas telah selesai', '#28a745'),
('Dibatalkan', 'Tugas dibatalkan', '#dc3545');
