"""Source-level guard checks for critical PHP request boundaries.

These checks do not replace PHP/database integration tests; they run where no
PHP CLI or authoritative local schema is available.
"""
from pathlib import Path
import unittest


ROOT = Path(__file__).resolve().parents[1]


class SecurityRegressionTests(unittest.TestCase):
    def read(self, name):
        return (ROOT / name).read_text(encoding="utf-8")

    def test_state_changing_handlers_validate_csrf(self):
        for name in ("submitchores.php", "submitapproval.php", "changepassword.php", "payout.php", "logout.php"):
            with self.subTest(endpoint=name):
                self.assertIn("csrf_valid()", self.read(name))

    def test_form_routes_emit_csrf_tokens(self):
        for name in ("index.php", "approvals.php", "changepassword.php", "payout.php", "header.php"):
            with self.subTest(page=name):
                self.assertIn("csrf_field()", self.read(name))

    def test_login_rotates_session_identifier(self):
        self.assertIn("session_regenerate_id(true)", self.read("login.php"))
        self.assertIn("httponly", self.read("global.php"))
        self.assertIn("samesite", self.read("global.php"))

    def test_approval_handler_checks_parent_and_pending_state(self):
        source = self.read("submitapproval.php")
        self.assertIn("$_SESSION['parent']", source)
        self.assertIn("approved = 'N'", source)
        self.assertIn("ctype_digit", source)

    def test_payout_is_post_only_for_recording(self):
        source = self.read("payout.php")
        self.assertIn("$_SERVER['REQUEST_METHOD'] === 'POST'", source)
        self.assertIn("INSERT INTO chores_done", source)
        self.assertNotIn("$_GET[\"peopleid\"]", source)

    def test_external_configuration_does_not_embed_database_password(self):
        source = self.read("global.php")
        self.assertIn("CHORES_DB_PASSWORD", source)
        self.assertNotIn("Waterways1234", source)


if __name__ == "__main__":
    unittest.main()
