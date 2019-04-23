<?php

	class UntiResourceOwner implements \League\OAuth2\Client\Provider\ResourceOwnerInterface
	{
		public $id;
		public $username;
		public $email;
		public $firstname;
		public $lastname;
		public $middlename;
		public $dateOfBirth;
		public $gender;
		public $imageUrl;

		public function __construct (array $response)
		{
			$this->id = $response[ 'unti_id' ];
			$this->username = $response[ 'username' ];
			$this->email = $response[ 'email' ];
			$this->firstname = $response[ 'firstname' ];
			$this->middlename = $response[ 'secondname' ];
			$this->lastname = $response[ 'lastname' ];
			$this->dateOfBirth = $response[ 'date_of_birth' ];
			$this->gender = $response[ 'gender' ];

			if (!empty($response[ 'image' ]) && !empty($response[ 'image' ][ 'Original' ]))
			{
				$this->imageUrl = $response[ 'image' ][ 'Original' ];
			}
		}

		public function getId ()
		{
			return $this->id;
		}

		public function toArray ()
		{
			return [
				'email'      => $this->email,
				'firstname'  => $this->firstname,
				'middlename' => $this->middlename,
				'lastname'   => $this->lastname,
			];
		}
	}