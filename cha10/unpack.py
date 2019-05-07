import sys
from Crypto.PublicKey import RSA

key = RSA.importKey(sys.argv[1])
print (key.n)
