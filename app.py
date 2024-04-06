# import openai
# import streamlit as st
import os

# SYSTEM_PROMPT = "\n\nSystem: Your role is to understand and empathize with individuals considering foster parenting. You're here to provide reassurance, encouragement, and empowerment, helping them navigate their concerns and consider foster parenting more seriously. Aim to be a compassionate listener, offering insights and information that may alleviate worries and inspire confidence in their ability to make a positive impact in a child's life. Always respond with empathy, understanding, and positivity, while respecting the sensitivity of the subject. Personalize your responses by asking about the user's specific fears, hopes, or questions regarding foster care. This approach helps tailor your advice and support, making it more impactful and relevant to each individual's situation. Ask clarifying questions if necessary, but lean towards offering supportive and constructive responses that highlight the rewarding aspects of foster care."
# #openai.api_key = os.environ['OPENAI_API_KEY']
# with st.sidebar:
#     openai_api_key = st.text_input("OpenAI API Key", key="chatbot_api_key", type="password")
#     "[Get an OpenAI API key](https://platform.openai.com/account/api-keys)"
#     "[View the source code](https://github.com/streamlit/llm-examples/blob/main/Chatbot.py)"
#     "[![Open in GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://codespaces.new/streamlit/llm-examples?quickstart=1)"
# st.title("What type of foster parent could you be?")
# st.caption("Find out what impact you could have on a child's life. Answers and support tailored to your unique situation.")
# if "messages" not in st.session_state:
#     st.session_state["messages"] = [{"role": "assistant", "content": "Hello! I'm here to help you in whatever way you need, whether it be learning more about foster care, finding resources, easing your worries and doubts, or just chatting about what you're thinking. Let me know how I can best support you."}]

# for msg in st.session_state.messages:
#     st.chat_message(msg["role"]).write(msg["content"])

# openai_api_key = st.secrets["openai_api_key"]
# if prompt := st.chat_input():
#     if not openai_api_key:
#         st.info("Please add your OpenAI API key to continue.")
#         st.stop()

#     client = openai.OpenAI(api_key=openai_api_key)
#     st.session_state.messages.append({"role": "user", "content": prompt + SYSTEM_PROMPT})
#     st.chat_message("user").write(prompt)
#     response = client.chat.completions.create(model="gpt-3.5-turbo", messages=st.session_state.messages)
#     msg = response.choices[0].message.content
#     st.session_state.messages.append({"role": "assistant", "content": msg})
#     st.chat_message("assistant").write(msg)

from openai import OpenAI
import streamlit as st
from dotenv import load_dotenv

SYSTEM_PROMPT = "PLEASE CONSTRAIN RESPONSES TO BE MEDIUM LENGTH. As part of a foster care nonprofit, your mission is to gently and empathetically encourage individuals to consider becoming foster parents. You're here to listen, understand, and respond with kindness and encouragement, helping individuals navigate their concerns about foster parenting. By offering personalized, empathetic advice, you aim to inspire confidence and a sense of capability in potential foster parents. Address users' specific fears, hopes, and questions with gentle reassurance and constructive support, emphasizing the positive impact they can have on a child's life. Your responses should always be supportive, understanding, and rooted in the desire to help more people see themselves as capable foster parents, making a difference in children's lives. Be sensitive about their situation and be transparent about potential struggles yet supportive. If you don't have enough information, ask about their personal situation and what is holding them back. This is a sample response to a user doubting themselves and their capability: It’s completely understandable to feel uncertain about stepping into the role of a foster parent. It’s a significant decision that comes with its challenges, but also with immeasurable rewards. Many people feel unsure at the beginning, wondering if they have what it takes to provide a supportive and loving environment for a child in need. Remember, being a foster parent isn’t about being perfect; it’s about being present. It’s about offering stability, compassion, and support to a child during a time when they need it the most. The fact that you’re contemplating whether you can be a foster parent already shows a level of empathy and willingness to open your heart and home to a child in need. There are resources, training, and support networks available to help foster parents through this journey. These can provide guidance on how to navigate the challenges and how to best support the children in your care. What specific concerns or questions do you have about becoming a foster parent? I’m here to help address them and provide you with the information you might need to feel more confident in your decision."
load_dotenv()

st.title("Discover your impact as a foster parent.")
openai_api_key = os.environ.get('OPENAI_API_KEY')
client = OpenAI(api_key=openai_api_key)

if not openai_api_key:
    st.error("OpenAI API key is missing. Please add it to your .env file.")
    st.stop()

if "openai_model" not in st.session_state:
    st.session_state["openai_model"] = "gpt-3.5-turbo"

if "messages" not in st.session_state:
    st.session_state["messages"] = [
        {"role": "assistant", "content": "Hello! I'm here to help you in whatever way you need, whether it be learning more about foster care, finding resources, easing your worries and doubts, or just chatting about what you're thinking. Let me know how I can best support you."}]

for message in st.session_state.messages:
    with st.chat_message(message["role"]):
        st.markdown(message["content"])

if prompt := st.chat_input("What's on your mind? Tell us about your situation, fears, or hopes. We're here to help."):
    st.session_state.messages.append({"role": "user", "content": prompt + SYSTEM_PROMPT})
    with st.chat_message("user"):
        st.markdown(prompt)

    with st.chat_message("assistant"):
        stream = client.chat.completions.create(
            model=st.session_state["openai_model"],
            messages=[
                {"role": m["role"], "content": m["content"]}
                for m in st.session_state.messages
            ],
            stream=True,
        )
        response = st.write_stream(stream)
    st.session_state.messages.append({"role": "assistant", "content": response})
