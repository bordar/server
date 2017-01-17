<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.enum
 */
class BorhanDistributionProtocol extends BorhanEnum
{
	const FTP = 1;
	const SCP = 2;
	const SFTP = 3;
	const HTTP = 4;
	const HTTPS = 5;
	const ASPERA = 10;
}
