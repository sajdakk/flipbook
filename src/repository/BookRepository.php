<?php

require_once 'Repository.php';
require_once __DIR__ . '/../models/book/BookToDisplay.php';
require_once __DIR__ . '/../models/book/BookWriteRequest.php';

class BookRepository extends Repository
{
    public function getMayInterestYouBooks(): array
    {
        $stmt = $this->database::getInstance()->connect()->prepare('
        SELECT * FROM top_books_view
        LIMIT 3;
        ');
        $stmt->execute();
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($books as $book) {
            $result[] = new BookToDisplay(
                $book['id'],
                $book['title'],
                $book['author_string'],
                $book['average_mark'],
                $book['rate_count'],
                $book['genre_id'],
                $book['language_id'],
                $book['date_of_publication'],
                $book['page_count'],
                $book['image'],
                $book['isbn_number'],
                $book['description'],
                $book['upload_date'],
                $book['accept_date'],
                $book['created_by'],
                $book['reject_date']

            );
        }

        return $result;
    }
    public function getTopBooks(): array
    {
        $stmt = $this->database::getInstance()->connect()->prepare('
        SELECT * FROM top_books_view;
        ');

        $stmt->execute();

        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($books as $book) {
            $result[] = new BookToDisplay(
                $book['id'],
                $book['title'],
                $book['author_string'],
                $book['average_mark'],
                $book['rate_count'],
                $book['genre_id'],
                $book['language_id'],
                $book['date_of_publication'],
                $book['page_count'],
                $book['image'],
                $book['isbn_number'],
                $book['description'],
                $book['upload_date'],
                $book['accept_date'],
                $book['created_by'],
                $book['reject_date']

            );
        }

        return $result;
    }


    public function getFilteredBooks(?string $title, ?string $author_name, ?string $author_surname): array
    {
        $title = strtolower($title);
        $author_name = strtolower($author_name);
        $author_surname = strtolower($author_surname);

        $query = '
        SELECT
        b.*,
        COALESCE(author_names.author_string, \'\') AS author_string,
        COALESCE(AVG(r.rate), 0) AS average_rate,
        COUNT(r.id) AS rate_count
    FROM
        books b
    JOIN (
        SELECT
            ab.book_id,
            string_agg(a.name || \' \' || a.surname, \', \') AS author_string
        FROM
            author_book ab
        JOIN
            authors a ON ab.author_id = a.id
        GROUP BY
            ab.book_id
    ) author_names ON b.id = author_names.book_id
    LEFT JOIN
        reviews r ON b.id = r.book_id
    WHERE
        (COALESCE(:title, \'\') = \'\' OR LOWER(b.title) LIKE :titleWildcard)
        AND (COALESCE(:author_name, \'\') = \'\' OR LOWER(author_names.author_string) LIKE :authorNameWildcard)
        AND (COALESCE(:author_surname, \'\') = \'\' OR LOWER(author_names.author_string) LIKE :authorSurnameWildcard)
        AND r.accept_date IS NOT NULL

    GROUP BY
        b.id, author_names.author_string;
        ';



        $stmt = $this->database::getInstance()->connect()->prepare($query);

        // Bind parameters
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':titleWildcard', '%' . $title . '%', PDO::PARAM_STR); // Use wildcard for partial matching
        $stmt->bindParam(':author_name', $author_name, PDO::PARAM_STR);
        $stmt->bindValue(':authorNameWildcard', '%' . $author_name . '%', PDO::PARAM_STR); // Use wildcard for partial matching
        $stmt->bindParam(':author_surname', $author_surname, PDO::PARAM_STR);
        $stmt->bindValue(':authorSurnameWildcard', '%' . $author_surname . '%', PDO::PARAM_STR); // Use wildcard for partial matching

        // Execute the query
        $stmt->execute();

        // Fetch the results
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        // Process the results
        foreach ($books as $book) {
            $result[] = new BookToDisplay(
                $book['id'],
                $book['title'],
                $book['author_string'],
                $book['average_rate'],
                $book['rate_count'],
                $book['genre_id'],
                $book['language_id'],
                $book['date_of_publication'],
                $book['page_count'],
                $book['image'],
                $book['isbn_number'],
                $book['description'],
                $book['upload_date'],
                $book['accept_date'],
                $book['created_by'],
                $book['reject_date']

            );
        }

        return $result;
    }
    public function getBookFromId(string $bookId): ?BookToDisplay
    {
        $stmt = $this->database::getInstance()->connect()->prepare('
        SELECT
        b.*,
        a.author_string,
        COALESCE(AVG(r.rate), 0) AS average_mark,
        COUNT(r.rate) AS rate_count
    FROM
        books b
    LEFT JOIN (
        SELECT
            ba.book_id,
            string_agg(author.name || \' \' || author.surname, \', \') AS author_string
        FROM
            authors AS author
        JOIN
            author_book AS ba ON author.id = ba.author_id
        GROUP BY
            ba.book_id
    ) AS a ON b.id = a.book_id
    LEFT JOIN (
        SELECT
            book_id,
            rate
        FROM
            reviews
        WHERE
            accept_date IS NOT NULL
    ) r ON b.id = r.book_id
    WHERE
    b.id = :book_id
    GROUP BY
        b.id, a.author_string
    ORDER BY
        average_mark DESC
        ');
        $stmt->bindParam(':book_id', $bookId, PDO::PARAM_STR);
        $stmt->execute();

        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book == false) {
            return null;
        }

        return new BookToDisplay(
            $book['id'],
            $book['title'],
            $book['author_string'],
            $book['average_mark'],
            $book['rate_count'],
            $book['genre_id'],
            $book['language_id'],
            $book['date_of_publication'],
            $book['page_count'],
            $book['image'],
            $book['isbn_number'],
            $book['description'],
            $book['upload_date'],
            $book['accept_date'],
            $book['created_by'],
            $book['reject_date']
        );
    }

    public function getBooksToDisplayFromUserId(string $userId)
    {
        $stmt = $this->database::getInstance()->connect()->prepare('
            SELECT
                b.*,
                a.author_string,
                COALESCE(AVG(r.rate), 0) AS average_rate,
                COUNT(r.rate) AS rate_count
            FROM
                books b
            LEFT JOIN (
                SELECT
                    ba.book_id,
                    string_agg(author.name || \' \' || author.surname, \', \') AS author_string
                FROM
                    authors AS author
                JOIN
                    author_book AS ba ON author.id = ba.author_id
                GROUP BY
                    ba.book_id
            ) AS a ON b.id = a.book_id
            LEFT JOIN (
                SELECT
                    book_id,
                    rate
                FROM
                    reviews
                WHERE
                    accept_date IS NOT NULL
            ) r ON b.id = r.book_id
            WHERE
                b.created_by = :user_id
            GROUP BY
                b.id, a.author_string
        ');

        $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
        $stmt->execute();

        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($books as $book) {
            $result[] = new BookToDisplay(
                $book['id'],
                $book['title'],
                $book['author_string'],
                $book['average_rate'],
                $book['rate_count'],
                $book['genre_id'],
                $book['language_id'],
                $book['date_of_publication'],
                $book['page_count'],
                $book['image'],
                $book['isbn_number'],
                $book['description'],
                $book['upload_date'],
                $book['accept_date'],
                $book['created_by'],
                $book['reject_date']

            );
        }

        return $result;
    }


    public function getBooksToDisplayForAdmin()
    {
        $stmt = $this->database::getInstance()->connect()->prepare('
            SELECT
                b.*,
                a.author_string,
                COALESCE(AVG(r.rate), 0) AS average_rate,
                COUNT(r.rate) AS rate_count
            FROM
                books AS b
            LEFT JOIN (
                SELECT
                    ba.book_id,
                    string_agg(author.name || \' \' || author.surname, \', \') AS author_string
                FROM
                    authors AS author
                JOIN
                    author_book AS ba ON author.id = ba.author_id
                GROUP BY
                    ba.book_id
            ) AS a ON b.id = a.book_id
            LEFT JOIN (
                SELECT
                    book_id,
                    rate
                FROM
                    reviews
                WHERE
                    accept_date IS NOT NULL
            ) r ON b.id = r.book_id
            WHERE
                b.accept_date IS NULL AND b.reject_date IS NULL
            GROUP BY
                b.id, a.author_string
        ');

        $stmt->execute();

        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($books as $book) {
            $result[] = new BookToDisplay(
                $book['id'],
                $book['title'],
                $book['author_string'],
                $book['average_rate'],
                $book['rate_count'],
                $book['genre_id'],
                $book['language_id'],
                $book['date_of_publication'],
                $book['page_count'],
                $book['image'],
                $book['isbn_number'],
                $book['description'],
                $book['upload_date'],
                $book['accept_date'],
                $book['created_by'],
                $book['reject_date'],

            );
        }

        return $result;
    }

    public function addBook(BookWriteRequest $bookWriteRequest, $authors): ?int
    {
        // Convert PHP array to a string representation suitable for PostgreSQL
        $authorsString = '{' . implode(',', $authors) . '}';

        // Convert genreId to int
        $genreId = intval($bookWriteRequest->getGenreId());

        // Convert languageId to int
        $languageId = intval($bookWriteRequest->getLanguageId());


        $stmt = $this->database::getInstance()->connect()->prepare('
        SELECT add_book(
          ?,
        ?,
       ?,
          ?,
          ?,
          ?,
            ?,
         ?,
       ?,
   ?,
   ?
          )
        ');

        $success = $stmt->execute([
            $bookWriteRequest->getTitle(),
            $genreId,
            $languageId,
            $bookWriteRequest->getDateOfPublication(),
            $bookWriteRequest->getPageCount(),
            $bookWriteRequest->getImage(),
            $bookWriteRequest->getIsbnNumber(),
            $bookWriteRequest->getDescription(),
            $bookWriteRequest->getUploadDate(),
            $bookWriteRequest->getCreatedBy(),
            $authorsString
        ]);

        return $success;
    }

    public function acceptBookForBookId(string $bookId): bool
    {
        $stmt = $this->database::getInstance()->connect()->prepare('
        UPDATE books
        SET accept_date = CURRENT_TIMESTAMP
        WHERE id = :book_id
    ');

        $stmt->bindParam(':book_id', $bookId, PDO::PARAM_STR);

        try {
            $stmt->execute();
            return true; // Successful update
        } catch (PDOException $e) {
            // Handle the exception or log the error
            return false; // Update failed
        }
    }

    public function rejectBookForBookId(string $bookId): bool
    {
        $stmt = $this->database::getInstance()->connect()->prepare('
        UPDATE books
        SET reject_date = CURRENT_TIMESTAMP
        WHERE id = :book_id
    ');

        $stmt->bindParam(':book_id', $bookId, PDO::PARAM_STR);

        try {
            $stmt->execute();
            return true; // Successful update
        } catch (PDOException $e) {
            // Handle the exception or log the error
            return false; // Update failed
        }
    }
}