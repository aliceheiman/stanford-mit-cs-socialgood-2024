import argparse
import re

timestamp = re.compile("\d\d?:\d\d")

parser = argparse.ArgumentParser()
parser.add_argument("filename", type=str, help="name of transcript file")

args = parser.parse_args()

in_name = f"transcripts/{args.filename}.txt"
print(f"Parsing {in_name}...")
with open(in_name, "r") as f:
    content = f.read().splitlines()

transcript = ""
for i in range(len(content)):
    if content[i].startswith("["):
        continue
    if len(timestamp.findall(content[i])) != 0:
        continue
    if content[i] == "":
        continue

    transcript += content[i] + " "

transcript = transcript.capitalize()
out_name = f"transcripts/{args.filename}-clean.txt"
with open(out_name, "w") as f:
    f.write(transcript)

print(f"Saved clean transcript to {out_name}")

prompt = f"""
Enclosed in triple backticks (“””) is a transcript of a success story from a foster family. Summarize the transcript into a descriptive paragraph containing the family situation and background, the motivation behind the foster parents adopting the child, and the positive impact achieved.
“””
{transcript}
“””
Summary: """
prompt = prompt.strip()

out_name = f"prompts/{args.filename}.txt"
with open(out_name, "w") as f:
    f.write(prompt)

print(f"Saved prompt to {out_name}")
