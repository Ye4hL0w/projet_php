<?php
class User {
    private $id;
    private $email;
    private $password;
    private $firstName;
    private $lastName;
    private $birthdate;
    private $address;
    private $phone;
    private $backupEmail;

    // Getters and setters

    public function getId() {
        return $this->id;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    public function getBirthdate() {
        return $this->birthdate;
    }

    public function setBirthdate($birthdate) {
        $this->birthdate = $birthdate;
    }

    public function getAddress() {
        return $this->address;
    }

    public function setAddress($address) {
        $this->address = $address;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }

    public function getBackupEmail() {
        return $this->backupEmail;
    }

    public function setBackupEmail($backupEmail) {
        $this->backupEmail = $backupEmail;
    }
}

class Post {
    private $id;
    private $userId;
    private $title;
    private $content;
    private $images;
    private $visibility;

    // Getters and setters

    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function getImages() {
        return $this->images;
    }

    public function setImages($images) {
        $this->images = $images;
    }

    public function getVisibility() {
        return $this->visibility;
    }

    public function setVisibility($visibility) {
        $this->visibility = $visibility;
    }
}

class Like {
    private $id;
    private $postId;
    private $userId;
    private $comment;

    // Getters and setters

    public function getId() {
        return $this->id;
    }

    public function getPostId() {
        return $this->postId;
    }

    public function setPostId($postId) {
        $this->postId = $postId;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function getComment() {
        return $this->comment;
    }

    public function setComment($comment) {
        $this->comment = $comment;
    }
}

class FriendRequest {
    private $id;
    private $senderId;
    private $receiverId;
    private $status;

    // Getters and setters

    public function getId() {
        return $this->id;
    }

    public function getSenderId() {
        return $this->senderId;
    }

    public function setSenderId($senderId) {
        $this->senderId = $senderId;
    }

    public function getReceiverId() {
        return $this->receiverId;
    }

    public function setReceiverId($receiverId) {
        $this->receiverId = $receiverId;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }
}

class Admin {
    private $id;
    private $email;
    private $password;

    // Getters and setters

    public function getId() {
        return $this->id;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }
}

?>
