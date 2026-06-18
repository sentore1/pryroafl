# AI Rate Limit Guide - HTTP 429 Error

**Error:** "AI error (HTTP 429). Check your API key."

**Meaning:** You've exceeded the API rate limit (too many requests)

---

## 🎯 What is HTTP 429?

**HTTP 429 = "Too Many Requests"**

This means:
- ✅ Your API key is **valid and working**
- ❌ You've sent **too many requests too quickly**
- ⏱️ Temporarily **blocked** for a short time
- 🔄 **Will reset** automatically

---

## ⚡ Quick Fixes (Choose One)

### Fix #1: Wait 60 Seconds ⏱️
**Easiest solution:**
1. Stop using AI panel
2. Wait 1 minute
3. Try again
4. ✅ Works!

**Why:** Most rate limits reset every minute

### Fix #2: Switch AI Provider 🔄
**If still getting errors:**
1. Open AI Panel
2. Click ⚙️ **Settings**
3. **AI Provider** dropdown
4. Switch: **Groq** ↔ **OpenAI**
5. Click **Save UI Settings**
6. Try your question again

**Result:** Uses different API, different rate limit!

### Fix #3: Disable Auto-Refresh 📉
**If enabled:**
1. Open Settings
2. **Auto-Refresh** dropdown
3. Select **Disabled**
4. Save
5. Manual refresh only = fewer API calls

---

## 📊 Rate Limits Explained

### Groq (Free Tier)
```
Requests per Minute: 30
Requests per Day: 14,400
Requests per Month: ~430,000

Example:
- Ask AI 30 questions in 1 minute = BLOCKED
- Wait 1 minute = UNBLOCKED
- Ask 1 question every 2 seconds = NEVER BLOCKED
```

### OpenAI (Free Trial)
```
Requests per Minute: 3-5 (very limited!)
Requests per Day: 200
Tokens per Minute: 40,000

Example:
- Ask 3 questions quickly = BLOCKED
- Wait 1 minute = UNBLOCKED
- Very restrictive = Upgrade recommended
```

### OpenAI (Paid - Tier 1)
```
After spending $5+:
Requests per Minute: 60
Requests per Day: 10,000
Tokens per Minute: 200,000

Much better for production use!
```

---

## 🛠️ What I Fixed

### 1. Added Rate Limiting (2-Second Delay)
**Before:** You could spam requests instantly  
**After:** Minimum 2 seconds between requests

**Code Added:**
```php
// Prevents spam - enforces 2-second delay
if (time_since_last_request < 2 seconds) {
    Show: "Please wait X seconds"
    Block request
}
```

**Result:** Prevents you from hitting rate limits

### 2. Better Error Messages
**Before:**
```
AI error (HTTP 429). Check your API key.
```

**After:**
```
⏱️ Rate Limit Exceeded (HTTP 429)

You've sent too many requests too quickly!

Quick Fixes:
• Wait 60 seconds
• Switch AI Provider
• Disable Auto-Refresh

Why: Groq Free = 30 requests/minute limit

Long-term: Upgrade to paid plan
```

**Result:** Helpful, actionable guidance

---

## 💡 Prevention Tips

### DO:
- ✅ Wait 2-3 seconds between questions
- ✅ Use quick action buttons (fewer requests)
- ✅ Disable auto-refresh
- ✅ Think before asking (compose question carefully)
- ✅ Use paid tier for production

### DON'T:
- ❌ Spam questions rapidly
- ❌ Enable 30-second auto-refresh (too frequent)
- ❌ Keep refreshing when getting 429
- ❌ Open multiple AI panels simultaneously
- ❌ Use auto-briefing + auto-refresh together

---

## 📈 Upgrade Options

### Option A: Groq Pro (When Available)
**Status:** Currently in limited beta  
**Benefits:**
- Higher rate limits
- Priority access
- Faster responses

**How to get:**
- Check https://console.groq.com
- Join waitlist if available

### Option B: OpenAI Paid
**Cost:** Pay-as-you-go (very affordable)

**Pricing:**
- GPT-4o: $2.50 per 1M input tokens
- GPT-4o: $10 per 1M output tokens
- **Typical AI panel query:** ~$0.001 (1/10 of a cent!)

**How to upgrade:**
1. Go to https://platform.openai.com/account/billing
2. Add payment method
3. Add credits ($5 minimum)
4. ✅ Instant upgrade!

**Benefits:**
- 60 requests/minute (20x more!)
- 10,000 requests/day
- Higher token limits
- Priority access

**Cost for typical usage:**
- 100 questions/day = ~$0.10/day = $3/month
- Very affordable!

---

## 🔍 How to Check Your Usage

### Groq:
1. Go to https://console.groq.com
2. Login
3. Check "Usage" dashboard
4. See requests used today

### OpenAI:
1. Go to https://platform.openai.com/usage
2. View usage by day
3. See token consumption
4. Check rate limit tier

---

## ⚙️ Technical Details

### What Happens When You Hit Limit:

```
Request #1:  ✅ 200 OK
Request #2:  ✅ 200 OK
Request #3:  ✅ 200 OK
...
Request #30: ✅ 200 OK
Request #31: ❌ 429 Too Many Requests
Request #32: ❌ 429 Too Many Requests
... (wait 60 seconds) ...
Request #33: ✅ 200 OK (reset!)
```

### Rate Limit Headers (Technical):

APIs return these headers:
```
X-RateLimit-Limit: 30
X-RateLimit-Remaining: 5
X-RateLimit-Reset: 1640000000
Retry-After: 60
```

**Meaning:**
- Limit: 30 requests allowed
- Remaining: 5 left
- Reset: Unix timestamp when limit resets
- Retry-After: Wait 60 seconds

### Our Rate Limiter:

**Client-side (JavaScript):**
- 2-second minimum delay enforced

**Server-side (PHP):**
- Session-based tracking
- Prevents < 2-second requests
- Returns friendly message

---

## 🎯 Optimal Usage Patterns

### Light Usage (Free Tier OK):
```
Morning: Ask 5-10 questions
Afternoon: Ask 5-10 questions
Evening: Ask 5-10 questions

Total: 15-30 questions/day
Cost: FREE ✅
```

### Moderate Usage (Consider Paid):
```
Continuous monitoring
Auto-refresh every 5 minutes
100+ questions/day

Total: 100-200 questions/day
Cost: $3-5/month
```

### Heavy Usage (Paid Required):
```
Control center use
Real-time monitoring
Auto-refresh every 30 seconds
500+ questions/day

Total: 500-1000 questions/day
Cost: $10-20/month
```

---

## 🐛 Troubleshooting

### Problem: Getting 429 immediately

**Cause:** You hit limit earlier, still blocked

**Solution:**
1. Check time: Did you use AI in last minute?
2. Wait full 60 seconds
3. Try again
4. If still blocked, wait 5 minutes (full reset)

### Problem: 429 on every other request

**Cause:** Right at the edge of rate limit

**Solution:**
1. Slow down! Add 3-5 second delays
2. Disable auto-refresh
3. Switch to OpenAI (different limit pool)

### Problem: 429 but I only sent 3 requests

**Cause:** OpenAI free tier has very low limit (3-5/min)

**Solution:**
1. Switch to Groq (30/min)
2. Or upgrade OpenAI ($5 minimum)
3. Wait 60 seconds between questions

### Problem: 429 with paid OpenAI

**Cause:** Higher tier limits, but still exist

**Solution:**
1. Check usage dashboard
2. You might be on Tier 1 still (need to spend $50 for Tier 2)
3. Or hitting token limits (not request limits)
4. Wait and retry

---

## ✅ Best Practices

### For Development/Testing:
- Use Groq free tier (30/min is generous)
- Add 2-3 second delays
- Test thoroughly before deploying

### For Production:
- Upgrade to paid plan (both providers)
- Implement exponential backoff
- Cache common responses
- Monitor usage daily

### For End Users:
- Show rate limit warnings
- Display "wait time" countdown
- Auto-retry after delay
- Provide alternative actions

---

## 📊 Summary

**HTTP 429 Error:**
- ✅ Normal behavior for free APIs
- ✅ Your API key is working
- ✅ Just need to wait or upgrade
- ❌ Not a bug or problem

**Quick Fix:**
1. Wait 60 seconds
2. Try again
3. Works! ✨

**Long-term Fix:**
1. Upgrade to paid ($5 minimum)
2. Costs ~$0.001 per question
3. Never worry about limits again

**Prevention:**
1. 2-second delays (auto-enforced now!)
2. Disable auto-refresh
3. Think before asking
4. Use quick actions

---

**Updated:** June 18, 2026  
**Feature:** Rate Limiting & Better Error Messages  
**Status:** ✅ Fixed & Improved

