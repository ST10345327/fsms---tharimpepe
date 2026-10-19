-- Run once against the existing FSMS database before deploying the API token code.
-- The stored value is a SHA-256 hash; plaintext bearer tokens are never persisted.
CREATE TABLE IF NOT EXISTS ApiTokens (
    TokenID BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    TokenHash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    ExpiresAt DATETIME NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    RevokedAt DATETIME DEFAULT NULL,
    UNIQUE KEY uq_api_tokens_hash (TokenHash),
    KEY idx_api_tokens_user_expiry (UserID, RevokedAt, ExpiresAt),
    CONSTRAINT fk_api_tokens_user FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);
