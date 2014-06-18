<?php
/* ----------------------------------------
	Removed functions for <PHP5.3 compatability.
-----------------------------------------*/
/* ------------------------------
	Simple Bank Config Array.
-------------------------------*/

$simpleBankConfig = array (
	'depositFeePerc' => ( int ) getConfigValue ( 'sb_depositFee' ), // Deposit Fee Percentage.
	'withdrawFeePerc' => ( int ) getConfigValue ( 'sb_withdrawFee' ), // Withdrawl Fee Percentage.
	'feeRequirement' => ( int ) getConfigValue ( 'sb_feeRequiredAmount' ), // Amount Required before fees are taken. ( 10 default )
	'interestRequirement' => ( int ) getConfigValue ( 'sb_interestRequiredAmount' ), //Amount required before interest is given. ( 100 default )
	'interestPerc' => ( int ) getConfigValue ( 'sb_interestPerc' ), // Interest Percentage.
	'currencyTitle' => getConfigValue ( 'currencyStat', '' ), // Wrapper for the name of Currency in game.
	'bankTitle' => getConfigValue ( 'sb_bankTitle' ) // Title of Bank.
);
// Users bank amount.
$playerBankAmount = ( int ) getUserVariable ( sb_bankAmount );

//Handles form submits, for both deposits and withdrawls.
if ( array_key_exists ( 'deposit', $_GET ) || array_key_exists ( 'withdraw', $_GET ) ) {
	$method = array_key_exists ( 'deposit', $_GET ) ? 'deposit' : 'withdraw';

	if ( array_key_exists ( 'amount', $_POST ) && is_string ( $_POST['amount'] ) && filter_var ( $_POST['amount'], FILTER_VALIDATE_INT ) ) {
		
		$_POST['amount'] = abs ( filter_var ( $_POST['amount'], FILTER_SANITIZE_NUMBER_INT ) );
		
		$amount = $_POST['amount'];
		$fee = 0;
		$feePerc = ( $method == 'deposit' ) ? $simpleBankConfig['depositFeePerc'] : $simpleBankConfig['withdrawFeePerc'];
		
		//Calculate fees here, safe to do, comparison is done using $_POST.
		if ( $amount >= $simpleBankConfig['feeRequirement'] ) {
			if ( $feePerc > 0 ) {
				$fee = ( $amount / 100 ) * $feePerc;
				$amount = $amount - floor ( $fee );
			}
		}

		//Deposits.
		if ( $method == 'deposit' ) {
			if ( $userStats['!Currency']->value < $_POST['amount'] ) {
				$error = Translate ( 'You do not have this much %s to deposit.', $simpleBankConfig['currencyTitle'] );
			} else {
				$userStats['!Currency']->value = $userStats['!Currency']->value - $_POST['amount'];
				$playerBankAmount += $amount;
				$message = Translate ( 'You have deposited %s %s ( Fee: %s Total: %s )', FormatNumber ( $_POST['amount'] ), $simpleBankConfig['currencyTitle'], FormatNumber ( $fee ), FormatNumber ( $amount ) );
			}
		} else {
			//Withdrawls.
			if ( $playerBankAmount < $_POST['amount'] ) {
				$error = Translate ( 'There is not enough %s in the bank to withdraw this much.', $simpleBankConfig['currencyTitle'] );
			} else {
				$userStats['!Currency']->value = $userStats['!Currency']->value + $amount;
				$playerBankAmount = $playerBankAmount - $_POST['amount'];
				$message = Translate ( 'You have withdrawn %s %s ( Fee: %s Total: %s )', FormatNumber ( $_POST['amount'] ), $simpleBankConfig['currencyTitle'], FormatNumber ( $fee ), FormatNumber ( $amount ) );

			}
		}

		if ( isset ( $error ) ) {
			ErrorMessage ( $error );
		} else {
			ResultMessage ( $message );
			setUserVariable ( sb_bankAmount, $playerBankAmount );
		}

	} else {
		ErrorMessage ( 'Please enter a valid amount to '.$method );
	}
}



TableHeader ( $simpleBankConfig['bankTitle'], false ); ?>
<table class="plainTable" style="width:80%; margin: 0 auto;">
	<thead>
		<tr>
			<th colspan="2" style="text-align: center;">
				<?php echo Translate ( 
					'Welcome to %s, current interest rate is %u%% daily when balance is over %s %s, according to current balance %s %s per day', 
					$simpleBankConfig['bankTitle'], $simpleBankConfig['interestPerc'], $simpleBankConfig['interestRequirement'], $simpleBankConfig['currencyTitle'],
					( ( $playerBankAmount > $simpleBankConfig['interestRequirement'] ) ? FormatNumber ( floor ( ( $playerBankAmount / 100 ) * $simpleBankConfig['interestPerc'] ) ) : 0 ), $simpleBankConfig['currencyTitle'] 
				); ?>
			</th>
		</tr>
		<tr>
			<th><?php echo Translate ( 'Deposit:'); ?></th>
			<th><?php echo Translate ( 'Withdraw:'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td><?php echo Translate ( 'Fees on Deposit: %u%% on deposits %s %s or greater', $simpleBankConfig['depositFeePerc'], FormatNumber ( $simpleBankConfig['feeRequirement'] ), $simpleBankConfig['currencyTitle'] ); ?></td>
			<td><?php echo Translate ( 'Fees on Withdrawl: %u%% on withdrawls %s %s or greater', $simpleBankConfig['withdrawFeePerc'], FormatNumber ( $simpleBankConfig['feeRequirement'] ), $simpleBankConfig['currencyTitle'] ); ?></td>
		</tr>
	</tfoot>
	<tbody>
		<tr>
			<td>
				<p><?php echo Translate ( 'You currently have %s %s on your person.', FormatNumber ( $userStats['!Currency']->value ), $simpleBankConfig['currencyTitle'] ); ?></p>
				<form action="index.php?p=simple_banking&deposit" method="post">
					
					<p>
						<label for="dAmount"><?php echo Translate ( 'Amount to deposit:' ); ?></label><br />
						<input type="text" name="amount" id="dAmount" required />
					</p>
					
					<input type="submit" value="<?php echo Translate ( 'Deposit %s', $simpleBankConfig['currencyTitle'] ); ?>" />
				</form>
				
			</td>
			
			<td>	
				<p><?php echo Translate ( 'You currently have %s %s in the bank', FormatNumber ( $playerBankAmount ), $simpleBankConfig['currencyTitle'] ); ?></p>
				<form action="index.php?p=simple_banking&withdraw" method="post">
					
					<p>
						<label for="wAmount"><?php echo Translate ( 'Amount to Withdraw:' ); ?></label><br />
						<input type="text" name="amount" id="wAmount" required />
					</p>
					
					<input type="submit" value="<?php echo Translate ( 'Withdraw %s', $simpleBankConfig['currencyTitle'] ); ?>" />
				</form>
				
			</td>
		</tr>
	</tbody>
</table>
<?php TableFooter(); ?>