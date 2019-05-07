from fractions import gcd
from Crypto.PublicKey import RSA

def egcd(a, b):
	if a == 0:
		return (b, 0, 1)
	else:
		g, y, x = egcd(b % a, a)
		return (g, x - (b // a) * y, y)


def modinv(a, m):
	g, x, y = egcd(a, m)
	if g != 1:
		return False
	else:
		return x % m

rsa_pkeys = {
	"shanae": 0x00000000077373682d72736100000003010001000001005e6330deda8b56969e099f245535f00b7285ffa578de7e71d2cbcaa5c6482fbcd36d79637899b6a7cb905773586ca4169513aeff1c84e0baaf5ba962c612817435225923c3612dd740294498e6e8b36010126fa07210555b7a7127a5931cf763a2b3995fa399b7d87a298e01df4bd1dc1b261fd6fa3a59fed6d3b32b21fb5a3b479204b6b9f023e6aabd5e752a3d71faef02145e5f73d70148367d31cbce673a1293f9a22e9e328d633ad42a0bfa30257b976e43728e8fe87bd60fd4c459afbbeb39888f3370babdc7cc2a880e9203afe85df4f7fb8ec70dc51485edc75db0fb9c46c3a5d5da2ef4bfb8c40fdd8c3b634eaa8158b6d0fe30775e38630b1fbe47,
	"adriahuels": 0x00000000077373682d727361000000030100010000010100be483b00cf38a05bf1b12c37fb18ad7fa7f0cfce7aa0c388b8e3ce4a116877476e25f865370028b58ec12f415e5c8998f7e18527f6aabb5825b0a7d96003df9ecb4d865f565b6fc604d6b25e2307bd3da49f96b2a4f34a1ee632df6833c6e1833d35435367c08a41f02ee7f047c4b17b0838dc38de280525d28117821a474ca52b7712646a8df49d4c0855f15a2ba837d748a6fb2393a94f81c8f9b17b9c29790a6feee174884120afd45fc4aa1228879fdb12d268a6ba567e64fb29b2ef42caa0427f0a3b346ecd162af06ba97f1ee3eff36a6bb4936025a2e2a7f4364aa59ddb97cdd31369c7afbf6460470918386b8c3e464139c19d02eec25c453eed8219
}



for name1 in rsa_pkeys:
	for name2 in rsa_pkeys:
		if name1 != name2:
			cd = gcd(rsa_pkeys[name1], rsa_pkeys[name2])
			print "GCD for {} and {}: {}".format(name1, name2, cd)
			if cd > 1:
				n = rsa_pkeys[name1]
				p = cd
				q = n/p
				break

print "n: {:x}".format(n)
print "p: {:x}".format(p)
print "q: {:x}".format(q)

for e in [3, 5, 17, 257, 65537]:
	d = modinv(e, n - (p + q - 1))
	if d == False:
		continue

	key = RSA.construct((n, long(e), d, p, q))
	print(key.exportKey('PEM'))



