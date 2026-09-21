<input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey ?? (string) \Illuminate\Support\Str::uuid()) }}">
<div class="referral-form-grid">
    <label>রোগীর নাম<input name="name" required maxlength="120" autocomplete="name" value="{{ old('name') }}"></label>
    <label>রোগী / অভিভাবকের ফোন<input type="tel" name="phone" required maxlength="32" autocomplete="tel" value="{{ old('phone') }}"></label>
    <label>জেলা<input name="district" maxlength="100" value="{{ old('district') }}"></label>
    <label>হাসপাতাল<select name="hospital_id"><option value="">এখনো নির্বাচন হয়নি</option>@foreach($hospitals as $hospital)<option value="{{ $hospital->id }}" @selected(old('hospital_id') == $hospital->id)>{{ $hospital->name }}</option>@endforeach</select></label>
    <label>ট্রিটমেন্ট<select name="treatment_id"><option value="">এখনো নির্বাচন হয়নি</option>@foreach($treatments as $treatment)<option value="{{ $treatment->id }}" @selected(old('treatment_id') == $treatment->id)>{{ $treatment->name }}</option>@endforeach</select></label>
    <label class="referral-span">সহায়তার প্রয়োজন (সংক্ষেপে)<textarea name="request_summary" rows="3" maxlength="1000">{{ old('request_summary') }}</textarea></label>
</div>
<label class="referral-check"><input type="checkbox" name="contact_consent" value="1" required @checked(old('contact_consent'))><span>{{ ($public ?? false) ? 'আমি রোগী বা অনুমোদিত অভিভাবক। এই অনুরোধ নিয়ে Asian Health Connect আমার সঙ্গে যোগাযোগ করতে পারবে।' : 'রোগী বা অনুমোদিত অভিভাবক এই যোগাযোগের অনুরোধ পাঠাতে আমাকে অনুমতি দিয়েছেন।' }}</span></label>
<p class="referral-muted">শুধু প্রাথমিক যোগাযোগের অনুরোধ। মেডিকেল রিপোর্ট, পাসপোর্ট বা বিস্তারিত রোগের ইতিহাস এখানে দেবেন না। বিদেশের হাসপাতালে তথ্য পাঠাতে আলাদা সম্মতি নেওয়া হবে।</p>
