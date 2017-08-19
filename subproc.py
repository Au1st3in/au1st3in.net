import subprocess, sys
from subprocess import Popen

if __name__ == "__main__":
	try:
		cmd = []
		if '.py' in sys.argv[1]:
			cmd.append('python')
		for argv in sys.argv[1:]:
			cmd.append(argv)
		cmd.append('--subprocess')
		proc = Popen(cmd, shell=True, stdin=None, stdout=None, stderr=None, close_fds=True)
		sys.exit(0)
	except:
		sys.exit(1)