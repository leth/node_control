# Why chef?

Primarily, I was having trouble automating the mysql installer. The root password prompt always corrupted my terminal output.

Rather than spend the time fixing this, I thought I'd demo what chef can do.

# Whoa, that's a lot of cookbooks!

Every cookbook except the SOWN cookbook was installed with the `knife' utility (in the chef ruby gem) from the community cookbook repo.
NB: Cookbooks have full dependency graphs, so some of them may not be immediate dependencies of the sown cookbook.

