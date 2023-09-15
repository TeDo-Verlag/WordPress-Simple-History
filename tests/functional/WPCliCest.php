<?php

class WPCliCest {

	public function test_wp_cli_commands( FunctionalTester $I ) {
        // Test WP Version so we are not surprised by WP version changes.
        $I->cli('core version');
        $I->seeInShellOutput('6.1.1');

        $I->cli('simple-history');
        $I->seeInShellOutput('usage: wp simple-history list [--format=<format>] [--count=<count>]');
        
        $I->haveUserInDatabase(
            'luca', 
            'editor', 
            [
                'user_email' => 'luca@example.org',
                'user_pass' => 'passw0rd',
            ]
        );
        $I->loginAs('luca', 'passw0rd');

        $I->cli('simple-history list --count=1');
        $I->seeInShellOutput('ID	date	initiator	description	via	level	count');
        $I->seeInShellOutput('luca (luca@example.org)	Logged in		info	1');

        $result = $I->cliToString('simple-history list --format=json');
        $I->assertJson($result);
        // Test part of the JSON.
        $I->seeInShellOutput('"initiator":"luca (luca@example.org)","description":"Logged in","via":null,"level":"info","count":"1"}');
        $I->seeInShellOutput('"ID":"12"');
    }
    
    public function test_wp_cron( FunctionalTester $I ) {
        $I->cli('cron event list');
        $I->seeInShellOutput('simple_history/maybe_purge_db');
        $I->seeInShellOutput('simple_history/tests/cron');

        $I->cli('cron test');
        $I->seeInShellOutput('Success: WP-Cron spawning is working as expected.');

        $I->cli('cron event run simple_history/maybe_purge_db');
        $I->seeInShellOutput("Executed the cron event 'simple_history/maybe_purge_db' in");
        $I->seeInShellOutput("Success: Executed a total of 1 cron event.");

        $I->cli('cron event run simple_history/tests/cron');
        $I->cli('simple-history list --count=1');
        $I->seeInShellOutput('This is a log from a cron job');
        $I->seeInShellOutput('info');
        $I->seeInShellOutput('WP-CLI');
    }
}

