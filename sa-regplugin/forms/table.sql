CREATE TABLE `sa_user_info` (
  `user_id` int NOT NULL,
  `login_id` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gher_nav` varchar(50) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `birth_year` varchar(50) NOT NULL,
  `contact_mobile` varchar(10) NOT NULL,
  `contact_state` varchar(2) NOT NULL,
  `contact_country` varchar(2) NOT NULL,
  `isactive` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` varchar(50) NOT NULL,
  `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(50) NOT NULL,
  `updated_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `sa_user_members` (
  `mem_id` int NOT NULL,
  `user_id` int NOT NULL,
  `payment_id` int NOT NULL,
  `m_first_name` varchar(75) NOT NULL,
  `m_last_name` varchar(75) NOT NULL,
  `m_relationship` varchar(25) NOT NULL,
  `m_birthyear` varchar(4) NOT NULL,
  `m_gender` varchar(10) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(50) NOT NULL DEFAULT 'pc2023registration',
  `updated_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `sa_user_registration` (
  `reg_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` varchar(25) NOT NULL,
  `cancellation_reason` varchar(250) DEFAULT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(50) NOT NULL,
  `updated_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Table structure for table `sa_regprice_model`
--

CREATE TABLE `sa_regprice_model` (
  `regprice_model_id` int NOT NULL,
  `base_reg_price` int NOT NULL,
  `child_price` int NOT NULL,
  `child_agelimit` int NOT NULL,
  `kid_18below_price` int NOT NULL,
  `kid_agelimit` int NOT NULL,
  `adult_price` int NOT NULL,
  `discount_rate` float NOT NULL,
  `discount_desc` varchar(50) NOT NULL,
  `valid_until` date NOT NULL,
  `created_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(25) NOT NULL,
  `updated_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Table structure for table `sa_regprice_model`
--

ALTER TABLE `sa_user_info`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `sa_user_members`
--
ALTER TABLE `sa_user_members`
  ADD PRIMARY KEY (`mem_id`),
  ADD KEY `IDX_FAMILY_USER_ID` (`user_id`) USING BTREE,
  ADD KEY `IDX_FAMILY_PAYMENT_ID` (`payment_id`) USING BTREE;

  --
-- Indexes for table `sa_user_registration`
--
ALTER TABLE `sa_user_registration`
  ADD PRIMARY KEY (`reg_id`),
  ADD KEY `IDX_REGISTRATION_USERID` (`user_id`) USING BTREE;

ALTER TABLE `sa_user_members`
ADD CONSTRAINT `FK_FAMILY_USER_ID` FOREIGN KEY (`user_id`) REFERENCES `sa_user_info` (`user_id`) ON DELETE CASCADE;

--
-- Indexes for table `sa_regprice_model`
--
ALTER TABLE `sa_regprice_model`
  ADD PRIMARY KEY (`regprice_model_id`);

--
-- AUTO_INCREMENT for table `sa_user_info`
--
ALTER TABLE `sa_user_info`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sa_user_members`
--
ALTER TABLE `sa_user_members`
  MODIFY `mem_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sa_regprice_model`
--
ALTER TABLE `sa_regprice_model`
  MODIFY `regprice_model_id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `sa_user_members`
--
ALTER TABLE `sa_user_members`
  ADD CONSTRAINT `FK_USERMEMBER_USERID_STATUS` FOREIGN KEY (`user_id`) REFERENCES `sa_user_info` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `sa_user_registration`
--
ALTER TABLE `sa_user_registration`
  ADD CONSTRAINT `FK_USERID_STATUS` FOREIGN KEY (`user_id`) REFERENCES `sa_user_info` (`user_id`) ON DELETE CASCADE;

INSERT INTO `sa_regprice_model` (`regprice_model_id`, `base_reg_price`, `child_price`, `child_agelimit`, `kid_18below_price`, `kid_agelimit`, `adult_price`, `discount_rate`, `discount_desc`, `valid_until`, `created_ts`, `created_by`, `updated_ts`, `updated_by`) VALUES
(1, 0, 0, 5, 30, 12, 60, 1, 'EARLY BIRD', '2023-05-31', '2019-10-01 01:35:12', 'pc2020', '2023-04-08 16:08:29', 'pc2020');

